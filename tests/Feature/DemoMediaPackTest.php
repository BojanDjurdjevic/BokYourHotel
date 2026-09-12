<?php
namespace Tests\Feature;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\{DB,File,Storage};
use Tests\Support\CreatesBookingScenario;
use Tests\TestCase;
class DemoMediaPackTest extends TestCase
{
    use RefreshDatabase, CreatesBookingScenario;
    private string $root;
    protected function setUp(): void {
        parent::setUp(); $this->scenario(); Storage::fake('public');
        $this->root=storage_path('framework/testing/fictional-pack-'.bin2hex(random_bytes(8)));
        File::makeDirectory($this->root.'/images',0755,true);
        config(['demo.asset_directory'=>$this->root]);
        DB::table('demo_seed_runs')->insert(['name'=>'portfolio-v1','anchor_date'=>'2026-10-01','summary'=>json_encode(['hotel_ids'=>[$this->hotel->id]])]);
    }
    private function manifest(array $assets): void {
        file_put_contents($this->root.'/manifest.json',json_encode(['version'=>2,'categories'=>['exterior-city','suite'],'assets'=>$assets]));
    }
    protected function tearDown(): void {
        if (isset($this->root)) {
            foreach (['images/source.jpg','manifest.json'] as $file) if (is_file($this->root.'/'.$file)) unlink($this->root.'/'.$file);
            rmdir($this->root.'/images'); rmdir($this->root);
        }
        parent::tearDown();
    }
    public function test_missing_assets_report_counts_without_phantom_records(): void {
        $this->manifest([['filename'=>'missing.webp','category'=>'suite','source_type'=>'generated-demo-asset','license_note'=>'Test fixture','approved'=>true]]);
        $this->artisan('demo:images')->expectsOutputToContain('0/1 assets found')->expectsOutputToContain('0 hotels populated; 0 rooms populated')->assertSuccessful();
        $this->assertDatabaseCount('hotel_images',0); $this->assertDatabaseCount('room_images',0);
    }
    public function test_v2_provenance_validation_and_deterministic_repeat(): void {
        $image=\Illuminate\Http\UploadedFile::fake()->image('source.jpg');
        copy($image->getRealPath(),$this->root.'/images/source.jpg');
        $asset=['filename'=>'source.jpg','category'=>'exterior-city','source_type'=>'remote-url','license_note'=>'Generated test fixture','approved'=>true];
        $this->manifest([$asset]);
        $this->artisan('demo:images')->expectsOutputToContain('0 approved valid assets')->assertSuccessful();
        $this->assertDatabaseCount('hotel_images',0);
        $asset['source_type']='generated-demo-asset'; $this->manifest([$asset]);
        $this->artisan('demo:images')->expectsOutputToContain('1 hotels populated')->assertSuccessful();
        $this->artisan('demo:images')->assertSuccessful();
        $this->assertDatabaseCount('hotel_images',1);
        $this->assertDatabaseCount('room_images',0);
    }
    public function test_malformed_manifest_is_rejected_before_database_writes(): void {
        $this->manifest([['filename'=>['invalid'],'category'=>'suite']]);
        $this->expectException(\Illuminate\Validation\ValidationException::class);
        $this->artisan('demo:images')->run();
    }
}
