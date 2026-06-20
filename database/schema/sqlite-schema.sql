CREATE TABLE IF NOT EXISTS "migrations"(
  "id" integer primary key autoincrement not null,
  "migration" varchar not null,
  "batch" integer not null
);
CREATE TABLE IF NOT EXISTS "settings"(
  "id" integer primary key autoincrement not null,
  "space" varchar not null,
  "name" varchar not null,
  "value" text not null,
  "json" tinyint(1) not null default '0',
  "created_at" datetime,
  "updated_at" datetime
);
CREATE TABLE IF NOT EXISTS "addresses"(
  "id" integer primary key autoincrement not null,
  "customer_id" integer not null,
  "guest_id" varchar not null default '',
  "name" varchar not null,
  "email" varchar,
  "phone" varchar,
  "country_id" integer not null,
  "state_id" integer not null,
  "state" varchar not null,
  "city_id" integer,
  "city" varchar not null,
  "zipcode" varchar not null,
  "address_1" varchar not null,
  "address_2" varchar not null,
  "default" tinyint(1) not null default '0',
  "created_at" datetime,
  "updated_at" datetime
);
CREATE INDEX "addr_customer_id" on "addresses"("customer_id");
CREATE INDEX "addr_country_id" on "addresses"("country_id");
CREATE INDEX "addr_state_id" on "addresses"("state_id");
CREATE INDEX "addr_city_id" on "addresses"("city_id");
CREATE TABLE IF NOT EXISTS "admins"(
  "id" integer primary key autoincrement not null,
  "name" varchar not null,
  "email" varchar not null,
  "password" varchar not null,
  "locale" varchar not null default '',
  "active" tinyint(1) not null default '1',
  "created_at" datetime,
  "updated_at" datetime
);
CREATE UNIQUE INDEX "admins_email_unique" on "admins"("email");
CREATE TABLE IF NOT EXISTS "article_products"(
  "id" integer primary key autoincrement not null,
  "article_id" integer not null,
  "product_id" integer not null,
  "created_at" datetime,
  "updated_at" datetime
);
CREATE UNIQUE INDEX "unique_article_product" on "article_products"(
  "article_id",
  "product_id"
);
CREATE INDEX "ap_article_id" on "article_products"("article_id");
CREATE INDEX "ap_product_id" on "article_products"("product_id");
CREATE TABLE IF NOT EXISTS "article_tags"(
  "id" integer primary key autoincrement not null,
  "article_id" integer not null,
  "tag_id" integer not null,
  "created_at" datetime,
  "updated_at" datetime
);
CREATE TABLE IF NOT EXISTS "article_translations"(
  "id" integer primary key autoincrement not null,
  "article_id" integer not null,
  "locale" varchar not null,
  "title" varchar not null,
  "summary" varchar,
  "image" varchar,
  "content" text,
  "meta_title" varchar,
  "meta_description" varchar,
  "meta_keywords" varchar,
  "created_at" datetime,
  "updated_at" datetime
);
CREATE INDEX "at_article_id" on "article_translations"("article_id");
CREATE TABLE IF NOT EXISTS "articles"(
  "id" integer primary key autoincrement not null,
  "catalog_id" integer default '0',
  "slug" varchar,
  "position" integer not null default '0',
  "viewed" integer not null default '0',
  "author" varchar,
  "image" varchar,
  "active" tinyint(1) not null default '1',
  "created_at" datetime,
  "updated_at" datetime
);
CREATE INDEX "a_catalog_id" on "articles"("catalog_id");
CREATE UNIQUE INDEX "articles_slug_unique" on "articles"("slug");
CREATE TABLE IF NOT EXISTS "article_relations"(
  "id" integer primary key autoincrement not null,
  "article_id" integer not null,
  "relation_id" integer not null,
  "created_at" datetime,
  "updated_at" datetime
);
CREATE UNIQUE INDEX "unique_article_relation" on "article_relations"(
  "article_id",
  "relation_id"
);
CREATE INDEX "ar_article_id" on "article_relations"("article_id");
CREATE INDEX "ar_relation_id" on "article_relations"("relation_id");
CREATE TABLE IF NOT EXISTS "attribute_group_translations"(
  "id" integer primary key autoincrement not null,
  "attribute_group_id" integer not null,
  "locale" varchar not null default '',
  "name" varchar not null default '',
  "created_at" datetime,
  "updated_at" datetime
);
CREATE INDEX "attribute_group_id_locale" on "attribute_group_translations"(
  "attribute_group_id",
  "locale"
);
CREATE TABLE IF NOT EXISTS "attribute_groups"(
  "id" integer primary key autoincrement not null,
  "position" integer not null default '0',
  "created_at" datetime,
  "updated_at" datetime
);
CREATE TABLE IF NOT EXISTS "attribute_translations"(
  "id" integer primary key autoincrement not null,
  "attribute_id" integer not null,
  "locale" varchar not null default '',
  "name" varchar not null default '',
  "created_at" datetime,
  "updated_at" datetime
);
CREATE INDEX "attribute_id_locale" on "attribute_translations"(
  "attribute_id",
  "locale"
);
CREATE TABLE IF NOT EXISTS "attribute_value_translations"(
  "id" integer primary key autoincrement not null,
  "attribute_value_id" integer not null,
  "locale" varchar not null default '',
  "name" varchar not null default '',
  "created_at" datetime,
  "updated_at" datetime
);
CREATE INDEX "attribute_value_id_locale" on "attribute_value_translations"(
  "attribute_value_id",
  "locale"
);
CREATE INDEX "avt_attribute_value_id" on "attribute_value_translations"(
  "attribute_value_id"
);
CREATE TABLE IF NOT EXISTS "attribute_values"(
  "id" integer primary key autoincrement not null,
  "attribute_id" integer not null,
  "created_at" datetime,
  "updated_at" datetime
);
CREATE INDEX "av_attribute_id" on "attribute_values"("attribute_id");
CREATE TABLE IF NOT EXISTS "attributes"(
  "id" integer primary key autoincrement not null,
  "category_id" integer not null,
  "attribute_group_id" integer not null,
  "position" integer not null default '0',
  "created_at" datetime,
  "updated_at" datetime
);
CREATE INDEX "attr_attribute_group_id" on "attributes"("attribute_group_id");
CREATE TABLE IF NOT EXISTS "brands"(
  "id" integer primary key autoincrement not null,
  "name" varchar not null,
  "slug" varchar,
  "first" varchar not null,
  "logo" varchar not null,
  "position" integer not null default '0',
  "active" tinyint(1) not null default '1',
  "created_at" datetime,
  "updated_at" datetime
);
CREATE UNIQUE INDEX "brands_slug_unique" on "brands"("slug");
CREATE TABLE IF NOT EXISTS "cache"(
  "key" varchar not null,
  "value" text not null,
  "expiration" integer not null,
  primary key("key")
);
CREATE TABLE IF NOT EXISTS "cache_locks"(
  "key" varchar not null,
  "owner" varchar not null,
  "expiration" integer not null,
  primary key("key")
);
CREATE TABLE IF NOT EXISTS "cart_items"(
  "id" integer primary key autoincrement not null,
  "customer_id" integer not null,
  "product_id" integer not null,
  "sku_code" varchar not null,
  "guest_id" varchar not null default '',
  "selected" tinyint(1) not null,
  "quantity" integer not null,
  "item_type" varchar not null default 'normal',
  "reference" text,
  "created_at" datetime,
  "updated_at" datetime
);
CREATE INDEX "ci_customer_id" on "cart_items"("customer_id");
CREATE INDEX "ci_product_id" on "cart_items"("product_id");
CREATE INDEX "ci_sku_code" on "cart_items"("sku_code");
CREATE TABLE IF NOT EXISTS "catalog_translations"(
  "id" integer primary key autoincrement not null,
  "catalog_id" integer not null,
  "locale" varchar not null,
  "title" varchar not null,
  "summary" text,
  "meta_title" varchar,
  "meta_description" varchar,
  "meta_keywords" varchar,
  "created_at" datetime,
  "updated_at" datetime
);
CREATE INDEX "ct_catalog_id" on "catalog_translations"("catalog_id");
CREATE TABLE IF NOT EXISTS "catalogs"(
  "id" integer primary key autoincrement not null,
  "parent_id" integer not null default '0',
  "slug" varchar,
  "position" integer not null default '0',
  "active" tinyint(1) not null default '1',
  "created_at" datetime,
  "updated_at" datetime
);
CREATE INDEX "c_parent_id" on "catalogs"("parent_id");
CREATE UNIQUE INDEX "catalogs_slug_unique" on "catalogs"("slug");
CREATE TABLE IF NOT EXISTS "categories"(
  "id" integer primary key autoincrement not null,
  "parent_id" integer not null default '0',
  "slug" varchar,
  "image" text,
  "position" integer not null default '0',
  "active" tinyint(1) not null default '1',
  "created_at" datetime,
  "updated_at" datetime
);
CREATE INDEX "cat_parent_id" on "categories"("parent_id");
CREATE UNIQUE INDEX "categories_slug_unique" on "categories"("slug");
CREATE TABLE IF NOT EXISTS "category_paths"(
  "id" integer primary key autoincrement not null,
  "category_id" integer not null,
  "path_id" integer not null,
  "level" integer not null,
  "created_at" datetime,
  "updated_at" datetime
);
CREATE INDEX "cp_category_id" on "category_paths"("category_id");
CREATE INDEX "cp_path_id" on "category_paths"("path_id");
CREATE TABLE IF NOT EXISTS "category_translations"(
  "id" integer primary key autoincrement not null,
  "category_id" integer not null,
  "locale" varchar not null,
  "name" varchar not null,
  "summary" text,
  "content" text,
  "meta_title" varchar,
  "meta_description" varchar,
  "meta_keywords" varchar,
  "created_at" datetime,
  "updated_at" datetime
);
CREATE INDEX "catt_category_id" on "category_translations"("category_id");
CREATE TABLE IF NOT EXISTS "checkout"(
  "id" integer primary key autoincrement not null,
  "customer_id" integer not null,
  "guest_id" varchar not null default '',
  "shipping_address_id" integer not null,
  "shipping_method_code" varchar not null,
  "billing_address_id" integer not null,
  "billing_method_code" varchar not null,
  "comment" text,
  "reference" text,
  "created_at" datetime,
  "updated_at" datetime
);
CREATE INDEX "cart_customer_id" on "checkout"("customer_id");
CREATE INDEX "c_sa_id" on "checkout"("shipping_address_id");
CREATE INDEX "c_ba_id" on "checkout"("billing_address_id");
CREATE TABLE IF NOT EXISTS "countries"(
  "id" integer primary key autoincrement not null,
  "name" varchar not null,
  "code" varchar not null,
  "continent" varchar not null,
  "position" integer not null default '0',
  "active" tinyint(1) not null default '1',
  "created_at" datetime,
  "updated_at" datetime
);
CREATE TABLE IF NOT EXISTS "currencies"(
  "id" integer primary key autoincrement not null,
  "name" varchar not null,
  "code" varchar not null,
  "symbol_left" varchar not null,
  "symbol_right" varchar not null,
  "decimal_place" varchar not null,
  "value" double not null,
  "active" tinyint(1) not null default '1',
  "created_at" datetime,
  "updated_at" datetime
);
CREATE TABLE IF NOT EXISTS "customer_favorites"(
  "id" integer primary key autoincrement not null,
  "customer_id" integer not null,
  "product_id" integer not null,
  "created_at" datetime,
  "updated_at" datetime
);
CREATE INDEX "cw_customer_id" on "customer_favorites"("customer_id");
CREATE INDEX "cw_product_id" on "customer_favorites"("product_id");
CREATE TABLE IF NOT EXISTS "customer_group_translations"(
  "id" integer primary key autoincrement not null,
  "customer_group_id" integer not null,
  "locale" varchar not null,
  "name" varchar not null,
  "description" text,
  "created_at" datetime,
  "updated_at" datetime
);
CREATE INDEX "cgt_customer_group_id" on "customer_group_translations"(
  "customer_group_id"
);
CREATE TABLE IF NOT EXISTS "customer_groups"(
  "id" integer primary key autoincrement not null,
  "level" integer not null,
  "mini_cost" numeric not null,
  "discount_rate" numeric not null,
  "created_at" datetime,
  "updated_at" datetime
);
CREATE TABLE IF NOT EXISTS "customer_socials"(
  "id" integer primary key autoincrement not null,
  "customer_id" integer not null,
  "provider" varchar not null,
  "user_id" varchar not null,
  "union_id" varchar not null,
  "access_token" text not null,
  "refresh_token" text not null,
  "reference" text not null,
  "created_at" datetime,
  "updated_at" datetime
);
CREATE INDEX "cs_customer_id" on "customer_socials"("customer_id");
CREATE TABLE IF NOT EXISTS "customers"(
  "id" integer primary key autoincrement not null,
  "email" varchar,
  "calling_code" varchar,
  "telephone" varchar,
  "password" varchar not null,
  "name" varchar not null,
  "balance" numeric not null default '0',
  "avatar" varchar not null default '',
  "customer_group_id" integer not null default '0',
  "address_id" integer not null default '0',
  "locale" varchar not null default '',
  "active" tinyint(1) not null default '1',
  "code" varchar not null default '',
  "from" varchar not null default '',
  "deleted_at" datetime,
  "created_at" datetime,
  "updated_at" datetime
);
CREATE INDEX "cust_calling_telephone" on "customers"(
  "calling_code",
  "telephone"
);
CREATE UNIQUE INDEX "customers_email_unique" on "customers"("email");
CREATE INDEX "c_customer_group_id" on "customers"("customer_group_id");
CREATE INDEX "c_address_id" on "customers"("address_id");
CREATE TABLE IF NOT EXISTS "customer_transactions"(
  "id" integer primary key autoincrement not null,
  "customer_id" integer not null,
  "amount" numeric not null,
  "type" varchar not null,
  "comment" text,
  "balance" numeric,
  "created_at" datetime,
  "updated_at" datetime
);
CREATE INDEX "ct_customer_id" on "customer_transactions"("customer_id");
CREATE TABLE IF NOT EXISTS "customer_withdrawals"(
  "id" integer primary key autoincrement not null,
  "customer_id" integer not null,
  "amount" numeric not null default '0',
  "account_type" varchar not null default 'bank',
  "account_number" varchar,
  "bank_name" varchar,
  "bank_account" varchar,
  "status" varchar not null default 'pending',
  "comment" text,
  "admin_comment" text,
  "created_at" datetime,
  "updated_at" datetime
);
CREATE INDEX "cwd_customer_status" on "customer_withdrawals"(
  "customer_id",
  "status"
);
CREATE INDEX "cwd_status_created" on "customer_withdrawals"(
  "status",
  "created_at"
);
CREATE INDEX "cwd_customer_id" on "customer_withdrawals"("customer_id");
CREATE TABLE IF NOT EXISTS "failed_jobs"(
  "id" integer primary key autoincrement not null,
  "uuid" varchar not null,
  "connection" text not null,
  "queue" text not null,
  "payload" text not null,
  "exception" text not null,
  "failed_at" datetime not null default CURRENT_TIMESTAMP
);
CREATE UNIQUE INDEX "failed_jobs_uuid_unique" on "failed_jobs"("uuid");
CREATE TABLE IF NOT EXISTS "locales"(
  "id" integer primary key autoincrement not null,
  "name" varchar not null,
  "code" varchar not null,
  "image" varchar not null,
  "position" integer not null default '0',
  "active" tinyint(1) not null default '1',
  "created_at" datetime,
  "updated_at" datetime
);
CREATE TABLE IF NOT EXISTS "notifications"(
  "id" varchar not null,
  "type" varchar not null,
  "notifiable_type" varchar not null,
  "notifiable_id" integer not null,
  "data" text not null,
  "read_at" datetime,
  "created_at" datetime,
  "updated_at" datetime,
  primary key("id")
);
CREATE INDEX "notif_notifiable" on "notifications"(
  "notifiable_type",
  "notifiable_id"
);
CREATE TABLE IF NOT EXISTS "order_fees"(
  "id" integer primary key autoincrement not null,
  "order_id" integer not null,
  "code" varchar not null,
  "value" numeric not null,
  "title" varchar not null,
  "reference" text,
  "created_at" datetime,
  "updated_at" datetime
);
CREATE INDEX "ot_order_id" on "order_fees"("order_id");
CREATE TABLE IF NOT EXISTS "order_histories"(
  "id" integer primary key autoincrement not null,
  "order_id" integer not null,
  "status" varchar not null,
  "notify" tinyint(1) not null,
  "comment" text not null,
  "created_at" datetime,
  "updated_at" datetime
);
CREATE INDEX "oh_order_id" on "order_histories"("order_id");
CREATE TABLE IF NOT EXISTS "order_items"(
  "id" integer primary key autoincrement not null,
  "order_id" integer not null,
  "product_id" integer not null,
  "order_number" varchar not null,
  "product_sku" varchar not null,
  "variant_label" varchar not null,
  "name" varchar not null,
  "image" varchar not null,
  "quantity" integer not null,
  "price" numeric not null,
  "item_type" varchar not null default 'normal',
  "reference" text,
  "created_at" datetime,
  "updated_at" datetime,
  "deleted_at" datetime
);
CREATE INDEX "oi_order_id" on "order_items"("order_id");
CREATE INDEX "oi_product_id" on "order_items"("product_id");
CREATE TABLE IF NOT EXISTS "order_payments"(
  "id" integer primary key autoincrement not null,
  "order_id" integer not null,
  "charge_id" varchar not null,
  "amount" numeric not null,
  "handling_fee" numeric not null,
  "paid" tinyint(1) not null,
  "reference" text,
  "certificate" varchar,
  "created_at" datetime,
  "updated_at" datetime
);
CREATE INDEX "op_order_id" on "order_payments"("order_id");
CREATE TABLE IF NOT EXISTS "order_return_histories"(
  "id" integer primary key autoincrement not null,
  "order_return_id" integer not null,
  "status" varchar not null,
  "notify" tinyint(1) not null,
  "comment" text not null,
  "created_at" datetime,
  "updated_at" datetime
);
CREATE INDEX "orh_or_id" on "order_return_histories"("order_return_id");
CREATE TABLE IF NOT EXISTS "order_return_payments"(
  "id" integer primary key autoincrement not null,
  "order_return_id" integer not null,
  "amount" numeric not null,
  "type" varchar not null,
  "status" varchar not null,
  "comment" varchar not null,
  "created_at" datetime,
  "updated_at" datetime
);
CREATE INDEX "orp_order_return_id" on "order_return_payments"(
  "order_return_id"
);
CREATE TABLE IF NOT EXISTS "order_returns"(
  "id" integer primary key autoincrement not null,
  "customer_id" integer not null,
  "order_id" integer not null,
  "order_item_id" integer not null,
  "product_id" integer not null,
  "number" varchar not null,
  "order_number" varchar not null,
  "product_name" varchar not null,
  "product_sku" varchar not null,
  "opened" integer not null,
  "quantity" integer not null,
  "comment" text not null,
  "status" varchar not null,
  "created_at" datetime,
  "updated_at" datetime,
  "reason_id" integer not null default '0'
);
CREATE INDEX "or_customer_id" on "order_returns"("customer_id");
CREATE INDEX "or_order_id" on "order_returns"("order_id");
CREATE INDEX "ri_order_item_id" on "order_returns"("order_item_id");
CREATE INDEX "ri_product_id" on "order_returns"("product_id");
CREATE TABLE IF NOT EXISTS "order_shipments"(
  "id" integer primary key autoincrement not null,
  "order_id" integer not null,
  "express_code" varchar not null,
  "express_company" varchar not null,
  "express_number" varchar not null,
  "created_at" datetime,
  "updated_at" datetime,
  "warehouse_id" integer not null default '0',
  "warehouse_name" varchar not null default '',
  "status" varchar not null default 'pending',
  "shipped_at" datetime,
  "delivered_at" datetime
);
CREATE INDEX "os_order_id" on "order_shipments"("order_id");
CREATE TABLE IF NOT EXISTS "orders"(
  "id" integer primary key autoincrement not null,
  "number" varchar not null,
  "parent_id" integer not null default '0',
  "customer_id" integer not null,
  "customer_group_id" integer not null,
  "shipping_address_id" integer not null,
  "billing_address_id" integer not null,
  "customer_name" varchar not null,
  "email" varchar not null,
  "calling_code" integer not null,
  "telephone" varchar not null,
  "total" numeric not null,
  "locale" varchar not null,
  "currency_code" varchar not null,
  "currency_value" varchar not null,
  "ip" varchar not null,
  "user_agent" text not null,
  "status" varchar not null,
  "shipping_method_code" varchar not null,
  "shipping_method_name" varchar not null,
  "shipping_customer_name" varchar not null,
  "shipping_calling_code" varchar not null,
  "shipping_telephone" varchar not null,
  "shipping_country" varchar not null,
  "shipping_country_id" integer not null,
  "shipping_state_id" integer not null,
  "shipping_state" varchar not null,
  "shipping_city" varchar not null,
  "shipping_address_1" varchar not null,
  "shipping_address_2" varchar not null,
  "shipping_zipcode" varchar not null,
  "billing_method_code" varchar not null,
  "billing_method_name" varchar not null,
  "billing_customer_name" varchar not null,
  "billing_calling_code" varchar not null,
  "billing_telephone" varchar not null,
  "billing_country" varchar not null,
  "billing_country_id" integer not null,
  "billing_state_id" integer not null,
  "billing_state" varchar not null,
  "billing_city" varchar not null,
  "billing_address_1" varchar not null,
  "billing_address_2" varchar not null,
  "billing_zipcode" varchar not null,
  "comment" text,
  "admin_note" text,
  "created_at" datetime,
  "updated_at" datetime,
  "deleted_at" datetime
);
CREATE INDEX "o_customer_id" on "orders"("customer_id");
CREATE INDEX "o_cg_id" on "orders"("customer_group_id");
CREATE INDEX "o_sa_id" on "orders"("shipping_address_id");
CREATE INDEX "o_pa_id" on "orders"("billing_address_id");
CREATE TABLE IF NOT EXISTS "page_modules"(
  "id" integer primary key autoincrement not null,
  "page_id" integer not null,
  "module_data" text not null,
  "created_at" datetime,
  "updated_at" datetime
);
CREATE TABLE IF NOT EXISTS "page_translations"(
  "id" integer primary key autoincrement not null,
  "page_id" integer not null,
  "locale" varchar not null,
  "title" varchar not null,
  "content" text,
  "template" text,
  "meta_title" varchar,
  "meta_description" varchar,
  "meta_keywords" varchar,
  "created_at" datetime,
  "updated_at" datetime
);
CREATE INDEX "pt_article_id" on "page_translations"("page_id");
CREATE TABLE IF NOT EXISTS "pages"(
  "id" integer primary key autoincrement not null,
  "slug" varchar,
  "position" integer not null default '0',
  "viewed" integer not null default '0',
  "show_breadcrumb" tinyint(1) not null default '1',
  "active" tinyint(1) not null default '1',
  "created_at" datetime,
  "updated_at" datetime
);
CREATE UNIQUE INDEX "pages_slug_unique" on "pages"("slug");
CREATE TABLE IF NOT EXISTS "password_reset_tokens"(
  "email" varchar not null,
  "token" varchar not null,
  "created_at" datetime,
  primary key("email")
);
CREATE TABLE IF NOT EXISTS "permissions"(
  "id" integer primary key autoincrement not null,
  "name" varchar not null,
  "guard_name" varchar not null,
  "created_at" datetime,
  "updated_at" datetime
);
CREATE UNIQUE INDEX "perm_name_guard" on "permissions"("name", "guard_name");
CREATE TABLE IF NOT EXISTS "plugins"(
  "id" integer primary key autoincrement not null,
  "type" varchar not null,
  "code" varchar not null,
  "priority" integer not null,
  "created_at" datetime,
  "updated_at" datetime
);
CREATE TABLE IF NOT EXISTS "product_attributes"(
  "id" integer primary key autoincrement not null,
  "product_id" integer not null,
  "attribute_id" integer not null,
  "attribute_value_id" integer not null,
  "created_at" datetime,
  "updated_at" datetime
);
CREATE INDEX "pa_product_attribute_id" on "product_attributes"(
  "product_id",
  "attribute_id"
);
CREATE INDEX "pa_product_id" on "product_attributes"("product_id");
CREATE INDEX "pa_attribute_id" on "product_attributes"("attribute_id");
CREATE INDEX "pa_attribute_value_id" on "product_attributes"(
  "attribute_value_id"
);
CREATE TABLE IF NOT EXISTS "product_bundles"(
  "id" integer primary key autoincrement not null,
  "product_id" integer not null,
  "sku_id" integer not null,
  "quantity" integer not null,
  "created_at" datetime,
  "updated_at" datetime
);
CREATE TABLE IF NOT EXISTS "product_categories"(
  "id" integer primary key autoincrement not null,
  "product_id" integer not null,
  "category_id" integer not null
);
CREATE INDEX "pc_product_id" on "product_categories"("product_id");
CREATE INDEX "pc_category_id" on "product_categories"("category_id");
CREATE TABLE IF NOT EXISTS "product_images"(
  "id" integer primary key autoincrement not null,
  "product_id" integer not null,
  "path" varchar not null,
  "is_cover" tinyint(1) not null default '0',
  "belong_sku" tinyint(1) not null default '0',
  "position" integer not null default '0',
  "created_at" datetime,
  "updated_at" datetime
);
CREATE INDEX "pi_product_id" on "product_images"("product_id");
CREATE TABLE IF NOT EXISTS "product_relations"(
  "id" integer primary key autoincrement not null,
  "product_id" integer not null,
  "relation_id" integer not null,
  "created_at" datetime,
  "updated_at" datetime
);
CREATE INDEX "pr_product_id" on "product_relations"("product_id");
CREATE INDEX "pr_relation_id" on "product_relations"("relation_id");
CREATE TABLE IF NOT EXISTS "product_skus"(
  "id" integer primary key autoincrement not null,
  "product_id" integer not null,
  "images" text,
  "variants" text,
  "code" varchar not null,
  "model" varchar not null default '',
  "price" double not null default '0',
  "origin_price" double not null default '0',
  "quantity" integer not null default '0',
  "is_default" tinyint(1) not null,
  "position" integer not null default '0',
  "created_at" datetime,
  "updated_at" datetime
);
CREATE INDEX "ps_product_id" on "product_skus"("product_id");
CREATE UNIQUE INDEX "sku_code" on "product_skus"("code");
CREATE TABLE IF NOT EXISTS "product_translations"(
  "id" integer primary key autoincrement not null,
  "product_id" integer not null,
  "locale" varchar not null,
  "name" varchar not null,
  "summary" text,
  "selling_point" text,
  "content" text,
  "meta_title" varchar,
  "meta_description" varchar,
  "meta_keywords" varchar,
  "created_at" datetime,
  "updated_at" datetime
);
CREATE INDEX "pt_product_id" on "product_translations"("product_id");
CREATE TABLE IF NOT EXISTS "products"(
  "id" integer primary key autoincrement not null,
  "brand_id" integer not null,
  "images" text,
  "hover_image" varchar,
  "video" text,
  "price" numeric not null default '0',
  "type" varchar not null default 'normal',
  "tax_class_id" integer not null default '0',
  "spu_code" varchar,
  "slug" varchar,
  "variables" text,
  "is_virtual" tinyint(1) not null default '0',
  "position" integer not null default '0',
  "active" tinyint(1) not null default '1',
  "weight" numeric not null default '0',
  "weight_class" varchar not null default '',
  "sales" integer not null default '0',
  "viewed" integer not null default '0',
  "published_at" datetime,
  "deleted_at" datetime,
  "created_at" datetime,
  "updated_at" datetime
);
CREATE INDEX "prod_brand_id" on "products"("brand_id");
CREATE INDEX "p_tc_id" on "products"("tax_class_id");
CREATE UNIQUE INDEX "products_spu_code_unique" on "products"("spu_code");
CREATE UNIQUE INDEX "products_slug_unique" on "products"("slug");
CREATE TABLE IF NOT EXISTS "region_states"(
  "id" integer primary key autoincrement not null,
  "region_id" integer not null,
  "country_id" integer not null,
  "state_id" integer not null,
  "created_at" datetime,
  "updated_at" datetime
);
CREATE INDEX "rs_region_id" on "region_states"("region_id");
CREATE INDEX "rs_country_id" on "region_states"("country_id");
CREATE INDEX "rs_state_id" on "region_states"("state_id");
CREATE TABLE IF NOT EXISTS "regions"(
  "id" integer primary key autoincrement not null,
  "name" varchar not null,
  "description" varchar,
  "position" integer not null default '0',
  "active" tinyint(1) not null default '1',
  "created_at" datetime,
  "updated_at" datetime
);
CREATE TABLE IF NOT EXISTS "reviews"(
  "id" integer primary key autoincrement not null,
  "customer_id" integer,
  "product_id" integer,
  "order_item_id" integer,
  "rating" integer not null,
  "content" varchar not null,
  "like" integer not null,
  "dislike" integer not null,
  "active" tinyint(1) not null default '1',
  "created_at" datetime,
  "updated_at" datetime
);
CREATE INDEX "rv_customer_id" on "reviews"("customer_id");
CREATE INDEX "rv_product_id" on "reviews"("product_id");
CREATE INDEX "rv_oi_id" on "reviews"("order_item_id");
CREATE TABLE IF NOT EXISTS "roles"(
  "id" integer primary key autoincrement not null,
  "name" varchar not null,
  "guard_name" varchar not null,
  "created_at" datetime,
  "updated_at" datetime
);
CREATE UNIQUE INDEX "role_name_guard" on "roles"("name", "guard_name");
CREATE TABLE IF NOT EXISTS "sessions"(
  "id" varchar not null,
  "user_id" integer,
  "ip_address" varchar,
  "user_agent" text,
  "payload" text not null,
  "last_activity" integer not null,
  primary key("id")
);
CREATE INDEX "sessions_user_id_index" on "sessions"("user_id");
CREATE INDEX "sessions_last_activity_index" on "sessions"("last_activity");
CREATE TABLE IF NOT EXISTS "states"(
  "id" integer primary key autoincrement not null,
  "country_id" integer not null,
  "country_code" varchar not null,
  "name" varchar not null,
  "code" varchar not null,
  "position" integer not null default '0',
  "active" tinyint(1) not null default '1',
  "created_at" datetime,
  "updated_at" datetime
);
CREATE INDEX "s_country_id" on "states"("country_id");
CREATE TABLE IF NOT EXISTS "tag_translations"(
  "id" integer primary key autoincrement not null,
  "tag_id" integer,
  "locale" varchar not null,
  "name" varchar not null,
  "created_at" datetime,
  "updated_at" datetime
);
CREATE TABLE IF NOT EXISTS "tags"(
  "id" integer primary key autoincrement not null,
  "slug" varchar,
  "position" integer not null default '0',
  "active" tinyint(1) not null default '1',
  "created_at" datetime,
  "updated_at" datetime
);
CREATE UNIQUE INDEX "tags_slug_unique" on "tags"("slug");
CREATE TABLE IF NOT EXISTS "tax_classes"(
  "id" integer primary key autoincrement not null,
  "name" varchar not null,
  "description" varchar,
  "created_at" datetime,
  "updated_at" datetime
);
CREATE TABLE IF NOT EXISTS "tax_rates"(
  "id" integer primary key autoincrement not null,
  "region_id" integer not null,
  "name" varchar not null,
  "type" varchar check("type" in('fixed', 'percent')) not null,
  "rate" numeric not null,
  "created_at" datetime,
  "updated_at" datetime
);
CREATE INDEX "tr_region_id" on "tax_rates"("region_id");
CREATE TABLE IF NOT EXISTS "tax_rules"(
  "id" integer primary key autoincrement not null,
  "tax_class_id" integer not null,
  "tax_rate_id" integer not null,
  "based" varchar check("based" in('shipping', 'billing', 'store')) not null,
  "priority" integer not null,
  "created_at" datetime,
  "updated_at" datetime
);
CREATE INDEX "tr_tax_class_id" on "tax_rules"("tax_class_id");
CREATE INDEX "tr_tax_rate_id" on "tax_rules"("tax_rate_id");
CREATE TABLE IF NOT EXISTS "verify_codes"(
  "id" integer primary key autoincrement not null,
  "account" varchar not null,
  "code" varchar not null,
  "type" varchar not null default 'register',
  "deleted_at" datetime,
  "created_at" datetime,
  "updated_at" datetime
);
CREATE TABLE IF NOT EXISTS "weight_classes"(
  "id" integer primary key autoincrement not null,
  "code" varchar not null,
  "name" varchar not null,
  "unit" varchar not null,
  "value" numeric not null default '1',
  "position" integer not null default '0',
  "active" tinyint(1) not null default '1',
  "created_at" datetime,
  "updated_at" datetime
);
CREATE UNIQUE INDEX "weight_classes_code_unique" on "weight_classes"("code");
CREATE TABLE IF NOT EXISTS "options"(
  "id" integer primary key autoincrement not null,
  "name" text not null,
  "description" text,
  "type" varchar check("type" in('select', 'radio', 'checkbox')) not null default 'select',
  "position" integer not null default '0',
  "active" tinyint(1) not null default '1',
  "required" tinyint(1) not null default '0',
  "created_at" datetime,
  "updated_at" datetime
);
CREATE TABLE IF NOT EXISTS "option_values"(
  "id" integer primary key autoincrement not null,
  "option_id" integer not null,
  "name" text not null,
  "image" varchar,
  "position" integer not null default '0',
  "active" tinyint(1) not null default '1',
  "created_at" datetime,
  "updated_at" datetime
);
CREATE INDEX "ov_option_id" on "option_values"("option_id");
CREATE TABLE IF NOT EXISTS "product_options"(
  "id" integer primary key autoincrement not null,
  "product_id" integer not null,
  "option_id" integer not null,
  "position" integer not null default '0',
  "created_at" datetime,
  "updated_at" datetime
);
CREATE UNIQUE INDEX "po_product_option_unique" on "product_options"(
  "product_id",
  "option_id"
);
CREATE INDEX "po_product_id" on "product_options"("product_id");
CREATE INDEX "po_option_id" on "product_options"("option_id");
CREATE TABLE IF NOT EXISTS "product_option_values"(
  "id" integer primary key autoincrement not null,
  "product_id" integer not null,
  "option_id" integer not null,
  "option_value_id" integer not null,
  "price_adjustment" numeric not null default '0',
  "quantity" integer not null default '0',
  "subtract_stock" tinyint(1) not null default '0',
  "created_at" datetime,
  "updated_at" datetime
);
CREATE UNIQUE INDEX "product_option_values_unique" on "product_option_values"(
  "product_id",
  "option_id",
  "option_value_id"
);
CREATE INDEX "pov_product_id" on "product_option_values"("product_id");
CREATE INDEX "pov_option_id" on "product_option_values"("option_id");
CREATE INDEX "pov_option_value_id" on "product_option_values"(
  "option_value_id"
);
CREATE TABLE IF NOT EXISTS "cart_option_values"(
  "id" integer primary key autoincrement not null,
  "cart_item_id" integer not null,
  "option_id" integer not null,
  "option_value_id" integer not null,
  "option_name" text not null,
  "option_value_name" text not null,
  "price_adjustment" numeric not null default '0',
  "created_at" datetime,
  "updated_at" datetime
);
CREATE INDEX "cov_cart_item_id" on "cart_option_values"("cart_item_id");
CREATE INDEX "cov_option_id" on "cart_option_values"("option_id");
CREATE INDEX "cov_option_value_id" on "cart_option_values"("option_value_id");
CREATE TABLE IF NOT EXISTS "order_option_values"(
  "id" integer primary key autoincrement not null,
  "order_item_id" integer not null,
  "option_id" integer not null,
  "option_value_id" integer not null,
  "option_name" varchar not null,
  "option_value_name" varchar not null,
  "price_adjustment" numeric not null default '0',
  "created_at" datetime,
  "updated_at" datetime
);
CREATE INDEX "oov_order_item_id" on "order_option_values"("order_item_id");
CREATE INDEX "oov_option_id" on "order_option_values"("option_id");
CREATE INDEX "oov_option_value_id" on "order_option_values"("option_value_id");
CREATE TABLE IF NOT EXISTS "model_has_permissions"(
  "permission_id" integer not null,
  "model_type" varchar not null,
  "model_id" integer not null,
  foreign key("permission_id") references "permissions"("id") on delete cascade on update restrict,
  primary key("permission_id", "model_id", "model_type")
);
CREATE INDEX "mhp_model_id_type" on "model_has_permissions"(
  "model_id",
  "model_type"
);
CREATE TABLE IF NOT EXISTS "model_has_roles"(
  "role_id" integer not null,
  "model_type" varchar not null,
  "model_id" integer not null,
  foreign key("role_id") references "roles"("id") on delete cascade on update restrict,
  primary key("role_id", "model_id", "model_type")
);
CREATE INDEX "mhr_model_id_type" on "model_has_roles"(
  "model_id",
  "model_type"
);
CREATE TABLE IF NOT EXISTS "role_has_permissions"(
  "permission_id" integer not null,
  "role_id" integer not null,
  foreign key("permission_id") references "permissions"("id") on delete cascade on update restrict,
  foreign key("role_id") references "roles"("id") on delete cascade on update restrict,
  primary key("permission_id", "role_id")
);
CREATE INDEX "rhp_role" on "role_has_permissions"("role_id");
CREATE TABLE IF NOT EXISTS "jwt_tokens"(
  "id" integer primary key autoincrement not null,
  "token_id" varchar not null,
  "tokenable_type" varchar not null,
  "tokenable_id" integer not null,
  "guard" varchar not null,
  "device_name" varchar,
  "expires_at" datetime,
  "last_used_at" datetime,
  "created_at" datetime,
  "updated_at" datetime
);
CREATE INDEX "jwt_tokens_tokenable_type_tokenable_id_index" on "jwt_tokens"(
  "tokenable_type",
  "tokenable_id"
);
CREATE INDEX "jwt_tokens_tokenable_index" on "jwt_tokens"(
  "tokenable_type",
  "tokenable_id"
);
CREATE UNIQUE INDEX "jwt_tokens_token_id_unique" on "jwt_tokens"("token_id");
CREATE TABLE IF NOT EXISTS "jwt_blacklist"(
  "id" integer primary key autoincrement not null,
  "token_id" varchar not null,
  "expired_at" datetime not null,
  "created_at" datetime not null default CURRENT_TIMESTAMP
);
CREATE INDEX "jwt_blacklist_expired_index" on "jwt_blacklist"("expired_at");
CREATE UNIQUE INDEX "jwt_blacklist_token_id_unique" on "jwt_blacklist"(
  "token_id"
);
CREATE TABLE IF NOT EXISTS "warehouses"(
  "id" integer primary key autoincrement not null,
  "code" varchar not null,
  "name" varchar not null,
  "description" text,
  "contact_name" varchar not null default '',
  "contact_phone" varchar not null default '',
  "country_id" integer not null default '0',
  "country" varchar not null default '',
  "state_id" integer not null default '0',
  "state" varchar not null default '',
  "city" varchar not null default '',
  "address_1" varchar not null default '',
  "address_2" varchar not null default '',
  "zipcode" varchar not null default '',
  "latitude" numeric,
  "longitude" numeric,
  "priority" integer not null default '0',
  "is_default" tinyint(1) not null default '0',
  "active" tinyint(1) not null default '1',
  "deleted_at" datetime,
  "created_at" datetime,
  "updated_at" datetime
);
CREATE UNIQUE INDEX "warehouses_code_unique" on "warehouses"("code");
CREATE TABLE IF NOT EXISTS "warehouse_stocks"(
  "id" integer primary key autoincrement not null,
  "warehouse_id" integer not null,
  "product_id" integer not null default '0',
  "sku_id" integer not null default '0',
  "sku_code" varchar not null,
  "quantity" integer not null default '0',
  "reserved_quantity" integer not null default '0',
  "low_stock_threshold" integer not null default '0',
  "created_at" datetime,
  "updated_at" datetime
);
CREATE UNIQUE INDEX "ws_warehouse_sku" on "warehouse_stocks"(
  "warehouse_id",
  "sku_code"
);
CREATE INDEX "ws_warehouse_id" on "warehouse_stocks"("warehouse_id");
CREATE INDEX "ws_product_id" on "warehouse_stocks"("product_id");
CREATE INDEX "ws_sku_id" on "warehouse_stocks"("sku_id");
CREATE INDEX "ws_sku_code" on "warehouse_stocks"("sku_code");
CREATE TABLE IF NOT EXISTS "warehouse_stock_movements"(
  "id" integer primary key autoincrement not null,
  "warehouse_id" integer not null,
  "sku_code" varchar not null,
  "quantity" integer not null,
  "type" varchar not null,
  "reference_type" varchar not null default '',
  "reference_id" integer not null default '0',
  "note" varchar not null default '',
  "admin_id" integer not null default '0',
  "created_at" datetime,
  "updated_at" datetime
);
CREATE INDEX "wsm_reference" on "warehouse_stock_movements"(
  "reference_type",
  "reference_id"
);
CREATE INDEX "wsm_warehouse_id" on "warehouse_stock_movements"("warehouse_id");
CREATE INDEX "wsm_sku_code" on "warehouse_stock_movements"("sku_code");
CREATE TABLE IF NOT EXISTS "stock_transfers"(
  "id" integer primary key autoincrement not null,
  "number" varchar not null,
  "from_warehouse_id" integer not null,
  "to_warehouse_id" integer not null,
  "status" varchar not null default 'pending',
  "note" varchar not null default '',
  "admin_id" integer not null default '0',
  "shipped_at" datetime,
  "completed_at" datetime,
  "created_at" datetime,
  "updated_at" datetime
);
CREATE UNIQUE INDEX "stock_transfers_number_unique" on "stock_transfers"(
  "number"
);
CREATE INDEX "st_from_wh" on "stock_transfers"("from_warehouse_id");
CREATE INDEX "st_to_wh" on "stock_transfers"("to_warehouse_id");
CREATE INDEX "st_status" on "stock_transfers"("status");
CREATE TABLE IF NOT EXISTS "stock_transfer_items"(
  "id" integer primary key autoincrement not null,
  "stock_transfer_id" integer not null,
  "sku_code" varchar not null,
  "quantity" integer not null,
  "received_quantity" integer not null default '0',
  "created_at" datetime,
  "updated_at" datetime
);
CREATE INDEX "sti_transfer_id" on "stock_transfer_items"("stock_transfer_id");
CREATE TABLE IF NOT EXISTS "order_shipment_items"(
  "id" integer primary key autoincrement not null,
  "shipment_id" integer not null,
  "order_item_id" integer not null,
  "sku_code" varchar not null,
  "quantity" integer not null,
  "created_at" datetime,
  "updated_at" datetime
);
CREATE INDEX "osi_shipment_id" on "order_shipment_items"("shipment_id");
CREATE INDEX "osi_order_item_id" on "order_shipment_items"("order_item_id");
CREATE TABLE IF NOT EXISTS "activity_log"(
  "id" integer primary key autoincrement not null,
  "log_name" varchar,
  "description" text not null,
  "subject_type" varchar,
  "subject_id" integer,
  "causer_type" varchar,
  "causer_id" integer,
  "properties" text,
  "created_at" datetime,
  "updated_at" datetime,
  "event" varchar,
  "batch_uuid" varchar
);
CREATE INDEX "subject" on "activity_log"("subject_type", "subject_id");
CREATE INDEX "causer" on "activity_log"("causer_type", "causer_id");
CREATE INDEX "activity_log_log_name_index" on "activity_log"("log_name");
CREATE TABLE IF NOT EXISTS "warehouse_service_areas"(
  "id" integer primary key autoincrement not null,
  "warehouse_id" integer not null,
  "country_id" integer not null,
  "state_id" integer not null default '0',
  "created_at" datetime,
  "updated_at" datetime
);
CREATE UNIQUE INDEX "wsa_unique" on "warehouse_service_areas"(
  "warehouse_id",
  "country_id",
  "state_id"
);
CREATE INDEX "wsa_warehouse_id" on "warehouse_service_areas"("warehouse_id");
CREATE TABLE IF NOT EXISTS "return_reasons"(
  "id" integer primary key autoincrement not null,
  "name" varchar not null,
  "description" varchar not null default '',
  "sort_order" integer not null default '0',
  "active" tinyint(1) not null default '1',
  "created_at" datetime,
  "updated_at" datetime
);
CREATE INDEX "o_number" on "orders"("number");
CREATE INDEX "o_status" on "orders"("status");

INSERT INTO migrations VALUES(1,'0001_01_01_000000_create_plugin_table',1);
INSERT INTO migrations VALUES(2,'2024_01_01_000000_init_niceshoply_table',1);
INSERT INTO migrations VALUES(3,'2026_02_07_000000_remove_database_queue_tables',1);
INSERT INTO migrations VALUES(4,'2026_02_07_100000_create_jwt_tables_and_remove_sanctum',1);
INSERT INTO migrations VALUES(5,'2026_02_11_000001_create_warehouse_tables',1);
INSERT INTO migrations VALUES(6,'2026_02_11_203245_create_activity_log_table',1);
INSERT INTO migrations VALUES(7,'2026_02_11_203246_add_event_column_to_activity_log_table',1);
INSERT INTO migrations VALUES(8,'2026_02_11_203247_add_batch_uuid_column_to_activity_log_table',1);
INSERT INTO migrations VALUES(9,'2026_02_12_000001_create_warehouse_service_areas_table',1);
INSERT INTO migrations VALUES(10,'2026_02_12_000002_create_return_reasons_table',1);
INSERT INTO migrations VALUES(11,'2026_02_12_000003_add_reason_id_to_order_returns_table',1);
INSERT INTO migrations VALUES(12,'2026_02_13_000000_add_order_indexes',1);
