# Laravel Code Review Report: Architecture & Database Design

This report evaluates the codebase of the Laravel project, focusing on folder structure, project architecture, database design, and models.

---

## 🔴 Critical Issues

### 1. Insecure Direct Object Reference (IDOR) on Cart Item Mutations
* **Files involved:**
  * [CartController.php](file:///g:/Real_Work/E-commerceWebsite/app/Http/Controllers/CartController.php#L52-L71)
  * [UpdateCartItemRequest.php](file:///g:/Real_Work/E-commerceWebsite/app/Http/Requests/Cart/UpdateCartItemRequest.php#L13-L16)
* **Why:** The endpoints for updating a cart item quantity (`CartController@update`) and deleting a cart item (`CartController@destroy`) receive a `CartItem` model instance directly through route model binding. However, neither the controller nor the form request validates whether the requested `CartItem` belongs to the current user's or guest's cart. The `authorize()` method in the form request simply returns `true`.
* **Impact:** Any user (or guest) can modify or delete cart items belonging to any other user in the system by guessing or passing a different `CartItem` ID.
* **Best Practice:** Always verify that the authenticated user owns or has permission to modify the specific resource instance requested in mutations.
* **Suggested Improvement:** Implement authorization logic inside `UpdateCartItemRequest@authorize` and a custom request or policy for the `destroy` action, confirming that the parent `Cart` of the `CartItem` belongs to the requesting user (`$cartItem->cart->user_id === $request->user()?->id`) or guest session.

### 2. Typographical Schema Corruption in Category Migration
* **Files involved:**
  * [2026_07_07_204328_create_categories_table.php](file:///g:/Real_Work/E-commerceWebsite/database/migrations/2026_07_07_204328_create_categories_table.php#L26)
  * [Category.php](file:///g:/Real_Work/E-commerceWebsite/app/Models/Category.php#L15)
* **Why:** The migration file contains `$table->text('  ')->nullable();` (literally two spaces) instead of defining the `'description'` column. Meanwhile, the `Category` Eloquent model includes `'description'` in its `$fillable` array.
* **Impact:** Attempts to create or update categories with a `description` field will fail with a SQL exception ("Column not found"), or the value will simply not be saved. Additionally, a column named `"  "` (two spaces) is created in the database, polluting the schema and potentially causing unexpected bugs.
* **Best Practice:** Ensure migration columns match model attribute names exactly and avoid empty string or whitespace-only column definitions.
* **Suggested Improvement:** Correct the column name in the migration to `'description'`.

---

## 🟠 High Issues

### 1. Cascade Failure on Soft-Deleted Parents (Products & Variants)
* **Files involved:**
  * [Product.php](file:///g:/Real_Work/E-commerceWebsite/app/Models/Product.php)
  * [ProductVariant.php](file:///g:/Real_Work/E-commerceWebsite/app/Models/ProductVariant.php)
  * [2026_07_11_180232_create_product_variants_table.php](file:///g:/Real_Work/E-commerceWebsite/database/migrations/2026_07_11_180232_create_product_variants_table.php#L13-L15)
* **Why:** Both `Product` and `ProductVariant` utilize the `SoftDeletes` trait. However, when a `Product` is soft-deleted, its related variants are not updated. They remain active (`deleted_at` stays `null`) in the database.
* **Impact:** Direct queries on `ProductVariant` (for search, inventory checks, or listing) will still fetch variants of soft-deleted products. Since the DB foreign key constraint uses `cascadeOnDelete()`, it only fires for hard deletes, leaving orphaned variants in the system on soft deletes.
* **Best Practice:** In systems using soft deletes, child models must be soft-deleted in cascade via application-level event listeners.
* **Suggested Improvement:** Bind a model event listener in the `boot()` method of `Product` to soft-delete related variants (`$product->variants()->delete()`) when the product is deleted, and restore them if the product is restored.

### 2. Database Constraint vs Soft Delete Conflict (Brand & Product)
* **Files involved:**
  * [Brand.php](file:///g:/Real_Work/E-commerceWebsite/app/Models/Brand.php)
  * [2026_07_11_082054_create_products_table.php](file:///g:/Real_Work/E-commerceWebsite/database/migrations/2026_07_11_082054_create_products_table.php#L18-L21)
* **Why:** In the `products` table, the `brand_id` foreign key is configured with `nullOnDelete()`. However, the `Brand` model uses `SoftDeletes`.
* **Impact:** When a brand is deleted, Eloquent performs a soft delete by updating the `deleted_at` timestamp. Because the database row is not actually deleted, the MySQL `ON DELETE SET NULL` database constraint is never triggered. The product's `brand_id` continues to point to the soft-deleted brand. Although Eloquent relationships will return `null` on the model, raw database integrity is degraded.
* **Best Practice:** Do not rely on database-level foreign key cascades for tables that implement soft deletes.
* **Suggested Improvement:** Handle the foreign key nullification explicitly in the application layer (e.g., in `BrandService` or via a `deleting` model event in the `Brand` model) to set `brand_id = null` for all associated products.

### 3. Completely Missing Order Business Logic Layer
* **Files involved:**
  * [Order.php](file:///g:/Real_Work/E-commerceWebsite/app/Models/Order.php)
  * [OrderItem.php](file:///g:/Real_Work/E-commerceWebsite/app/Models/OrderItem.php)
  * [OrderAddress.php](file:///g:/Real_Work/E-commerceWebsite/app/Models/OrderAddress.php)
  * [OrderStatusHistory.php](file:///g:/Real_Work/E-commerceWebsite/app/Models/OrderStatusHistory.php)
* **Why:** The database schema and models for `Order`, `OrderItem`, `OrderAddress`, and `OrderStatusHistory` have been fully designed and migrated, but there are no routes, controllers, or services to handle order creation, checkout, order views, or status tracking.
* **Impact:** The ordering system is dead code. The database tables exist, but the application is incapable of handling the checkout and purchase lifecycle.
* **Best Practice:** Do not introduce models and migrations without their corresponding controller and service layers.
* **Suggested Improvement:** Develop `OrderService`, `OrderController`, and customer routes to implement cart checkout and order management.

---

## 🟡 Medium Issues

### 1. Incomplete SoftDeletes Implementation on Category Model
* **Files involved:**
  * [Category.php](file:///g:/Real_Work/E-commerceWebsite/app/Models/Category.php)
  * [2026_07_07_204328_create_categories_table.php](file:///g:/Real_Work/E-commerceWebsite/database/migrations/2026_07_07_204328_create_categories_table.php#L32)
* **Why:** The `categories` migration defines `$table->softDeletes()`, but the `Category` model does not use the `SoftDeletes` trait.
* **Impact:** Calling `$category->delete()` will execute a hard SQL delete. This contradicts the schema design and renders the `deleted_at` column useless. Additionally, it causes hard deletes to fail with query exceptions due to product foreign key restrictions (`restrictOnDelete`).
* **Best Practice:** Eloquent model traits should always match the corresponding migration schema design.
* **Suggested Improvement:** Import and use the `Illuminate\Database\Eloquent\SoftDeletes` trait inside the `Category` model.

### 2. Orphaned Relations on Historical Order Items due to Soft-Deleted Variants
* **Files involved:**
  * [OrderItem.php](file:///g:/Real_Work/E-commerceWebsite/app/Models/OrderItem.php#L25-L28)
* **Why:** The `OrderItem` model defines a `variant()` relationship to `ProductVariant`. However, `ProductVariant` uses soft deletes.
* **Impact:** If a product variant is soft-deleted (e.g. out of stock/discontinued), loading the `variant` relationship on historical order items will return `null`. This breaks historical order history pages and administrative dashboards that look up the variant entity.
* **Best Practice:** Always use `withTrashed()` on relationships referencing models that support soft deletes when historical persistence is required.
* **Suggested Improvement:** Modify the relationship in `OrderItem` to return:
  `return $this->belongsTo(ProductVariant::class, 'product_variant_id')->withTrashed();`

### 3. Cart Merger Service Method is Unused (Dead Code)
* **Files involved:**
  * [CartService.php](file:///g:/Real_Work/E-commerceWebsite/app/Services/Cart/CartService.php#L76-L105)
  * [LoginController.php](file:///g:/Real_Work/E-commerceWebsite/app/Http/Controllers/Auth/LoginController.php)
  * [RegisterController.php](file:///g:/Real_Work/E-commerceWebsite/app/Http/Controllers/Auth/RegisterController.php)
* **Why:** The `CartService` defines a helper method `mergeGuestCartIntoUser()` designed to merge a guest's shopping cart into their account once they log in or register. However, this method is never invoked in either the `LoginController` or `RegisterController`.
* **Impact:** Items added to the cart by anonymous guests are permanently lost when they register or log in, resulting in poor user experience.
* **Best Practice:** Keep business services integrated into relevant controller workflows.
* **Suggested Improvement:** Invoke `mergeGuestCartIntoUser` inside the auth controllers after successful authentication, using the `X-Guest-Session-ID` header value.

### 4. Missing Database-Level Unique Constraints for Carts and Attributes
* **Files involved:**
  * [2026_07_12_082006_create_carts_table.php](file:///g:/Real_Work/E-commerceWebsite/database/migrations/2026_07_12_082006_create_carts_table.php#L14-L17)
  * [2026_07_08_195751_create_attribute_values_table.php](file:///g:/Real_Work/E-commerceWebsite/database/migrations/2026_07_08_195751_create_attribute_values_table.php)
* **Why:**
  1. The `carts` table does not have a unique index on the `user_id` column.
  2. The `attribute_values` table does not have a unique constraint on `(attribute_id, value)`.
* **Impact:**
  1. Without a unique constraint, race conditions during concurrent user operations could create multiple carts for a single user, violating the application logic.
  2. The application can store duplicate values (e.g. multiple "L" or "Red" entries) for the same attribute, polluting the table.
* **Best Practice:** Enforce fundamental data integrity constraints (like uniqueness) in the database schema, not just in Eloquent or Form Requests.
* **Suggested Improvement:**
  * Add a unique index to `user_id` in the `carts` table migration.
  * Add a unique index to `['attribute_id', 'value']` in the `attribute_values` table migration.

### 5. Missing Enum Casting for Order Status
* **Files involved:**
  * [Order.php](file:///g:/Real_Work/E-commerceWebsite/app/Models/Order.php#L26-L34)
  * [OrderStatus.php](file:///g:/Real_Work/E-commerceWebsite/app/Enums/OrderStatus.php)
* **Why:** An `OrderStatus` backed string enum is defined, but the `Order` model does not specify it in its attribute casting.
* **Impact:** The code lacks type safety when reading or writing the `order_status` attribute. Developers can assign arbitrary strings that are not defined in the enum, bypassing runtime checks.
* **Best Practice:** Cast string/integer status columns to their corresponding PHP Enums inside Eloquent models.
* **Suggested Improvement:** Add `'order_status' => OrderStatus::class` to the array returned by the `casts()` method in `Order`.

---

## 🟢 Low Issues

### 1. Inconsistent Controller Directory Organization
* **Files involved:**
  * [CartController.php](file:///g:/Real_Work/E-commerceWebsite/app/Http/Controllers/CartController.php)
* **Why:** The controller directory features subfolders like `Admin/` and `Auth/`. However, the customer-facing `CartController` is located at the root of `app/Http/Controllers`.
* **Impact:** Code organization is asymmetric. As the project grows, the root controller directory will become cluttered.
* **Best Practice:** Maintain a structured and symmetrical controller namespace (e.g. organizing customer-facing controllers under a `Customer` or `Front` directory).
* **Suggested Improvement:** Move `CartController` to `app/Http/Controllers/Customer/CartController.php` and update the routes file.

### 2. Inconsistent File Upload Implementation
* **Files involved:**
  * [BrandService.php](file:///g:/Real_Work/E-commerceWebsite/app/Services/Brand/BrandService.php#L21-L23)
  * [CategoryService.php](file:///g:/Real_Work/E-commerceWebsite/app/Services/Category/CategoryService.php#L35-L37)
* **Why:** `CategoryService` delegates image uploads to `ImageUploadService`, which handles resizing and converts images to WebP format. `BrandService` uploads brand logos directly using inline Eloquent storage calls (`$data['logo']->store()`).
* **Impact:** Brand logos miss optimization benefits (like WebP conversion and resizing) provided by `ImageUploadService`. Furthermore, storage implementation changes will require updating multiple service layers.
* **Best Practice:** Enforce a single, consistent file upload pipeline across all services.
* **Suggested Improvement:** Refactor `BrandService` to inject and utilize `ImageUploadService` for uploading logos.

### 3. Redundant Slug Validation and Service Mismatch
* **Files involved:**
  * [StoreProductRequest.php](file:///g:/Real_Work/E-commerceWebsite/app/Http/Requests/Product/StoreProductRequest.php#L30)
  * [ProductService.php](file:///g:/Real_Work/E-commerceWebsite/app/Services/Product/ProductService.php#L18-L23)
  * [UpdateProductRequest.php](file:///g:/Real_Work/E-commerceWebsite/app/Http/Requests/Product/UpdateProductRequest.php#L30)
* **Why:**
  1. `StoreProductRequest` validates the `slug` input. However, in `ProductService@create`, the service layer ignores any input slug and generates its own slug based on `name_en`.
  2. `UpdateProductRequest` lists `'slug' => ['nullable', 'string']` without checking uniqueness. If `name_en` is missing from the update parameters, the service updates the slug directly from the input without validating uniqueness, potentially causing SQL constraint crashes.
* **Impact:** Dead validation code on product store requests, and vulnerable slug updates on product update requests.
* **Best Practice:** Align request validation logic with the service layer behavior.
* **Suggested Improvement:** Remove the `slug` field validation from `StoreProductRequest`. Add `unique:products,slug` to `UpdateProductRequest` and ensure the service handles slug regeneration consistently.

### 4. Orphaned Files on Storage Disk upon Model Deletion
* **Files involved:**
  * [ProductService.php](file:///g:/Real_Work/E-commerceWebsite/app/Services/Product/ProductService.php#L36-L39)
  * [BrandService.php](file:///g:/Real_Work/E-commerceWebsite/app/Services/Brand/BrandService.php#L46-L49)
* **Why:** Deleting a `Product` (or soft-deleting a `Brand`/`Category` which later gets force-deleted) does not clean up associated images or logos from the storage disk.
* **Impact:** Over time, the storage disk accumulates unused image files (orphans), leading to high disk usage and bloated backups.
* **Best Practice:** Ensure that physical files are removed from the disk whenever their corresponding database records are permanently deleted.
* **Suggested Improvement:** Use model observers or add cleanup steps in the deletion methods of `ProductService` and `BrandService` to delete files using `Storage::delete` when the records are destroyed.

---

## 📊 Final Ratings

| Category | Rating | Summary of Core Reasons |
| :--- | :---: | :--- |
| **Folder Structure** | **8 / 10** | Clear separation of concerns with domain-grouped folders. Lost two points due to asymmetric controller placement (`CartController`). |
| **Project Architecture** | **6 / 10** | Good service layer adoption, but undermined by critical authorization gaps (IDOR), unused core methods, and completely unimplemented transactional features (orders). |
| **Database Design** | **5 / 10** | Heavy logical conflicts between Soft Deletes and foreign key cascades. A critical typo (`  ` column) in categories, and missing unique indexes on attributes/carts. |
| **Maintainability** | **6 / 10** | Standard Laravel constructs make it readable, but database-level gaps, storage leaks, and silent authorization bypasses complicate future maintenance. |
| **Scalability** | **7 / 10** | Structurally ready for modular growth due to clean file organization, but database integrity and state mismatch issues will trigger bugs under heavy traffic. |
