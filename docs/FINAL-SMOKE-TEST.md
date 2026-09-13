# BookYourHotel final smoke test

Run this checklist against the deployment-like local environment after setting `APP_ENV`, `APP_DEBUG`, database, storage, queue, mail and fake-payment configuration. Record the date, environment and any failed step.

## Final QA additions

- [ ] Public filters and supplier room forms expose one option per semantic board/facility; no `Demo` or snake_case label is visible.
- [ ] Search submit lands at the results anchor while a plain first page load stays at the top.
- [ ] Demo hotel cards use deterministic varied featured images; room cards use deterministic room-appropriate image sets.
- [ ] Recovery email is checked from the rendered link or decoded HTML, not by copying `&amp;` from a raw log line.
- [ ] User and supplier notification badges reflect queued database notifications after a worker processes the queue.
- [ ] Supplier Overview, Hotels, Bookings, Pending and Revenue share the same green Supplier Panel sidebar.
- [ ] Supplier inventory fields clearly say From, To, Available units and Price per night (EUR).
- [ ] Hotel and room image management show existing previews and support upload, featured selection and delete with ownership boundaries.
- [ ] Supplier sees Confirm booking for an eligible pending booking and Complete booking only when the existing lifecycle permits it.

For local database queues, run `php artisan queue:work --queue=default --tries=3 --timeout=60` while checking notifications. With `MAIL_MAILER=log`, validate the actual URL before HTML escaping or open the rendered link after decoding HTML entities.

## Public and guest

- [ ] Home loads with no debug text or broken assets.
- [ ] Destination autocomplete returns published cities, supports keyboard selection and handles an empty result.
- [ ] Hotel search works with city, country, dates, adults and children.
- [ ] Filters show canonical facility labels, `Any board type`, price placeholders and preserve selected values.
- [ ] Clear filters resets query state; pagination preserves active query parameters.
- [ ] Hotel cards show varied featured images with a consistent crop and working detail links.
- [ ] Hotel details show the description, rooms, availability CTA and sensible empty states.
- [ ] Hotel gallery opens from the first, a middle and the last image; arrows wrap around; Escape, X and backdrop close work.
- [ ] Room cards show only their own images; each room gallery opens, navigates and wraps independently.
- [ ] Fullscreen image viewers keep the image contained, lock background scrolling and remain usable on a phone.
- [ ] Availability rejects invalid or past dates and shows a useful no-rooms state.
- [ ] Booking form validates guest details, room selection, capacity and dates; a successful booking redirects to checkout.
- [ ] Fake payment success, failure and explicit retry show the correct state; no real payment is attempted.
- [ ] Voucher download/print renders booking, room, dates, totals, status and currency correctly.
- [ ] Voucher and checkout links preserve the correct guest booking and expire when expected.
- [ ] `Find my booking` returns the same generic response for a valid match, wrong email and missing booking.
- [ ] A valid guest recovery request delivers one signed management link to the booking email through the configured mail transport.
- [ ] A recovered link opens only that guest booking; expired or tampered links fail.

## Authenticated user

- [ ] Login, registration, logout and password recovery work.
- [ ] `My bookings` shows only the authenticated user's bookings.
- [ ] User checkout, payment, retry and cancellation respect the visible status and deadline.
- [ ] Voucher links and booking details belong to the selected booking.
- [ ] Notifications list, unread count and mark-as-read work; an empty list is clear.
- [ ] Voucher display and booking states use user-facing labels.

## Supplier

- [ ] Supplier dashboard counts and links load.
- [ ] Supplier hotel list, create, edit, setup and publish flows work.
- [ ] Room create/edit, facilities and room image upload/delete/featured actions are scoped to the supplier's hotel.
- [ ] Inventory calendar, single update, bulk update and stale-version errors work.
- [ ] Supplier bookings show owned hotels only and support permitted confirm, cancel and complete actions.

## Admin and superadmin

- [ ] Admin booking management lists accessible bookings and enforces permitted lifecycle actions.
- [ ] Superadmin dashboard and role-specific navigation work.
- [ ] Admin and supplier pages do not expose another hotel's rooms, images, inventory or bookings.

## Mobile and responsive layout

- [ ] Search fields stack cleanly on a phone with touch-sized controls.
- [ ] Filter panel can be opened and collapsed without covering results.
- [ ] Hotel and room galleries fit the viewport with readable controls and no background scroll.
- [ ] Navigation opens and closes from the mobile menu.
- [ ] Booking and payment forms remain usable without horizontal scrolling.

## Deployment checks

- [ ] `APP_DEBUG=false`, HTTPS and secure cookie/proxy settings are configured.
- [ ] `MAIL_MAILER=smtp` settings are present when real mail is required; credentials are supplied only through deployment secrets.
- [ ] Queue worker, failed-job monitoring and scheduler are running where queued notifications and booking expiry are enabled.
- [ ] `FAKE_PAYMENTS_ENABLED=false` unless this is an intentional portfolio/demo environment.
- [ ] Public storage is linked and all approved local demo images load.
- [ ] Backups, restore procedure, logs and basic monitoring have been verified.

## Presentation QA

### Theme

- [ ] A fresh browser starts in the dark theme.
- [ ] Toggle to light, refresh, and confirm light persists; toggle back to dark and confirm dark persists after refresh.
- [ ] No visible theme flash occurs during page load.
- [ ] Light theme uses a soft gray/slate background, readable controls and readable gallery/modal states.

### Supplier mobile navigation

- [ ] Desktop sidebar remains visible on desktop and hidden on mobile.
- [ ] Mobile supplier sub-navigation is visible below the global header.
- [ ] Overview, My Hotels, Bookings, Pending and Revenue are reachable, with the active section highlighted.
- [ ] Horizontal scrolling and keyboard focus work at 320–390px widths and on tablet layouts.
- [ ] Supplier navigation works without relying on the browser Back button.

### README

- [ ] Every README screenshot link points to an existing file.
- [ ] README commands match the local project and fake payment is clearly disclosed.
