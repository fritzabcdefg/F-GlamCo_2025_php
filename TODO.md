# TODO: Implement Review Edit/Update Flow

## Database Changes
- [x] Alter reviews table to add user_id column (nullable BIGINT UNSIGNED, FK to users.id)

## Backend Updates
- [x] Update product/reviews/store.php to insert user_id from $_SESSION['user_id'] if logged in
- [x] Create product/reviews/edit.php: Form to edit review, require login, check ownership
- [x] Create product/reviews/update.php: Handle POST update, validate ownership, update review fields

## Frontend Updates
- [x] Update product/show.php: Modify reviews query to include user_id, add edit link for user's reviews if logged in
- [x] Update admin/reviews.php: Add user_id column to the table display

## Testing
- [x] Run the ALTER TABLE SQL to update the database
- [ ] Test creating a review as logged-in user (should set user_id)
- [ ] Test editing a review (only owner should be able to)
- [ ] Test admin view shows user_id

## Optional
- [ ] Add edit links in user/profile.php for all user's reviews
