<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\{
    Customer,
    Staff,
    ProductCategory,
    StandardProduct,
    CustomizablePrintProduct,
    Clipart,
    PricingTier,
    WishlistItem,
    SavedDesign,
    ShoppingCart,
    CartItem,
    CustomerOrder,
    OrderItem,
    OrderNote,
    CarouselSlide
};

class TestFactories extends Command
{
    protected $signature = 'app:test-factories';
    protected $description = 'Test all model factories and their states';

    public function handle()
    {
        $this->info(" TESTING ALL FACTORS");
        $this->line("========================");

        $passed = 0;
        $failed = 0;
        $results = [];

        $runTest = function($name, $callback) use (&$passed, &$failed, &$results) {
            try {
                $callback();
                $passed++;
                $results[] = " PASS: $name";
            } catch (\Exception $e) {
                $failed++;
                $results[] = " FAIL: $name - " . $e->getMessage();
            }
        };

        // Clean up before starting
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        $tables = [
            'carousel_slides', 'order_notes', 'order_items', 'customer_orders', 'cart_items',
            'shopping_carts', 'saved_designs', 'wishlist_items', 'pricing_tiers', 'clipart',
            'customizable_print_products', 'standard_products', 'product_categories', 'staff', 'customers'
        ];
        foreach ($tables as $table) {
            DB::table($table)->truncate();
        }
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        $this->line("\n TESTING CUSTOMER FACTORY");
        $runTest("Customer default", fn() => Customer::factory()->create());
        $runTest("Customer inactive", fn() => Customer::factory()->inactive()->create());
        $runTest("Customer with pending reset", fn() => Customer::factory()->withPendingReset()->create());
        $runTest("Customer with expired reset", fn() => Customer::factory()->withExpiredReset()->create());
        $runTest("Customer full name too short", fn() => Customer::factory()->fullNameTooShort()->create());
        $runTest("Customer full name min length", fn() => Customer::factory()->fullNameMinLength()->create());
        $runTest("Customer full name mid length", fn() => Customer::factory()->fullNameMidLength()->create());
        $runTest("Customer full name max length", fn() => Customer::factory()->fullNameMaxLength()->create());
        $runTest("Customer full name too long", fn() => Customer::factory()->fullNameTooLong()->create());
        $runTest("Customer phone too short", fn() => Customer::factory()->phoneTooShort()->create());
        $runTest("Customer phone exact length", fn() => Customer::factory()->phoneExactLength()->create());
        $runTest("Customer phone too long", fn() => Customer::factory()->phoneTooLong()->create());
        $runTest("Customer without phone", fn() => Customer::factory()->withoutPhone()->create());

        $this->line("\n TESTING STAFF FACTORY");
        $runTest("Staff default", fn() => Staff::factory()->create());
        $runTest("Staff administrator", fn() => Staff::factory()->administrator()->create());
        $runTest("Staff inactive", fn() => Staff::factory()->inactive()->create());
        $runTest("Staff without full name", fn() => Staff::factory()->withoutFullName()->create());
        $runTest("Staff username empty", fn() => Staff::factory()->usernameEmpty()->create());
        $runTest("Staff username min length", fn() => Staff::factory()->usernameMinLength()->create());
        $runTest("Staff username mid length", fn() => Staff::factory()->usernameMidLength()->create());
        $runTest("Staff username max length", fn() => Staff::factory()->usernameMaxLength()->create());
        $runTest("Staff username too long", fn() => Staff::factory()->usernameTooLong()->create());
        $runTest("Staff password too short", fn() => Staff::factory()->passwordTooShort()->create());
        $runTest("Staff password min length", fn() => Staff::factory()->passwordMinLength()->create());
        $runTest("Staff password mid length", fn() => Staff::factory()->passwordMidLength()->create());
        $runTest("Staff password max length", fn() => Staff::factory()->passwordMaxLength()->create());
        $runTest("Staff password too long", fn() => Staff::factory()->passwordTooLong()->create());
        $runTest("Staff full name min length", fn() => Staff::factory()->fullNameMinLength()->create());
        $runTest("Staff full name max length", fn() => Staff::factory()->fullNameMaxLength()->create());
        $runTest("Staff full name too long", fn() => Staff::factory()->fullNameTooLong()->create());

        $this->line("\n TESTING PRODUCT CATEGORY FACTORY");
        $runTest("Product category default", fn() => ProductCategory::factory()->create());
        $runTest("Product category without description", fn() => ProductCategory::factory()->withoutDescription()->create());
        $runTest("Product category name too short", fn() => ProductCategory::factory()->nameTooShort()->create());
        $runTest("Product category name min length", fn() => ProductCategory::factory()->nameMinLength()->create());
        $runTest("Product category name mid length", fn() => ProductCategory::factory()->nameMidLength()->create());
        $runTest("Product category name max length", fn() => ProductCategory::factory()->nameMaxLength()->create());
        $runTest("Product category name too long", fn() => ProductCategory::factory()->nameTooLong()->create());
        $runTest("Product category description min length", fn() => ProductCategory::factory()->descriptionMinLength()->create());
        $runTest("Product category description mid length", fn() => ProductCategory::factory()->descriptionMidLength()->create());
        $runTest("Product category description max length", fn() => ProductCategory::factory()->descriptionMaxLength()->create());
        $runTest("Product category description too long", fn() => ProductCategory::factory()->descriptionTooLong()->create());

        $this->line("\n TESTING STANDARD PRODUCT FACTORY");
        $runTest("Standard product default", fn() => StandardProduct::factory()->create());
        $runTest("Standard product inactive", fn() => StandardProduct::factory()->inactive()->create());
        $runTest("Standard product uncategorised", fn() => StandardProduct::factory()->uncategorised()->create());
        $runTest("Standard product without image", fn() => StandardProduct::factory()->withoutImage()->create());
        $runTest("Standard product name too short", fn() => StandardProduct::factory()->nameTooShort()->create());
        $runTest("Standard product name min length", fn() => StandardProduct::factory()->nameMinLength()->create());
        $runTest("Standard product name mid length", fn() => StandardProduct::factory()->nameMidLength()->create());
        $runTest("Standard product name max length", fn() => StandardProduct::factory()->nameMaxLength()->create());
        $runTest("Standard product name too long", fn() => StandardProduct::factory()->nameTooLong()->create());
        $runTest("Standard product negative price", fn() => StandardProduct::factory()->negativePrice()->create());
        $runTest("Standard product free", fn() => StandardProduct::factory()->free()->create());
        $runTest("Standard product mid price", fn() => StandardProduct::factory()->midPrice()->create());
        $runTest("Standard product max price", fn() => StandardProduct::factory()->maxPrice()->create());
        $runTest("Standard product above max price", fn() => StandardProduct::factory()->aboveMaxPrice()->create());
        $runTest("Standard product description min length", fn() => StandardProduct::factory()->descriptionMinLength()->create());
        $runTest("Standard product description mid length", fn() => StandardProduct::factory()->descriptionMidLength()->create());
        $runTest("Standard product description max length", fn() => StandardProduct::factory()->descriptionMaxLength()->create());
        $runTest("Standard product description too long", fn() => StandardProduct::factory()->descriptionTooLong()->create());

        $this->line("\n TESTING CUSTOMIZABLE PRODUCT FACTORY");
        $runTest("Customizable product default", fn() => CustomizablePrintProduct::factory()->create());
        $runTest("Customizable product inactive", fn() => CustomizablePrintProduct::factory()->inactive()->create());
        $runTest("Customizable product without image", fn() => CustomizablePrintProduct::factory()->withoutImage()->create());
        $runTest("Customizable product with template config", fn() => CustomizablePrintProduct::factory()->withTemplateConfig()->create());
        $runTest("Customizable product name too short", fn() => CustomizablePrintProduct::factory()->nameTooShort()->create());
        $runTest("Customizable product name min length", fn() => CustomizablePrintProduct::factory()->nameMinLength()->create());
        $runTest("Customizable product name mid length", fn() => CustomizablePrintProduct::factory()->nameMidLength()->create());
        $runTest("Customizable product name max length", fn() => CustomizablePrintProduct::factory()->nameMaxLength()->create());
        $runTest("Customizable product name too long", fn() => CustomizablePrintProduct::factory()->nameTooLong()->create());
        $runTest("Customizable product description min length", fn() => CustomizablePrintProduct::factory()->descriptionMinLength()->create());
        $runTest("Customizable product description mid length", fn() => CustomizablePrintProduct::factory()->descriptionMidLength()->create());
        $runTest("Customizable product description max length", fn() => CustomizablePrintProduct::factory()->descriptionMaxLength()->create());
        $runTest("Customizable product description too long", fn() => CustomizablePrintProduct::factory()->descriptionTooLong()->create());

        $this->line("\n TESTING CLIPART FACTORY");
        $runTest("Clipart default 1", fn() => Clipart::factory()->create());
        $runTest("Clipart default 2", fn() => Clipart::factory()->create());

        $this->line("\n TESTING PRICING TIER FACTORY");
        $runTest("Pricing tier default", fn() => PricingTier::factory()->create());
        $runTest("Pricing tier first tier", fn() => PricingTier::factory()->firstTier()->create());
        $runTest("Pricing tier second tier", fn() => PricingTier::factory()->secondTier()->create());
        $runTest("Pricing tier third tier", fn() => PricingTier::factory()->thirdTier()->create());
        $runTest("Pricing tier min quantity below min", fn() => PricingTier::factory()->minimumQuantityBelowMin()->create());
        $runTest("Pricing tier min quantity at min", fn() => PricingTier::factory()->minimumQuantityAtMin()->create());
        $runTest("Pricing tier min quantity mid range", fn() => PricingTier::factory()->minimumQuantityMidRange()->create());
        $runTest("Pricing tier max quantity inverted", fn() => PricingTier::factory()->maximumQuantityInverted()->create());
        $runTest("Pricing tier max quantity equal to min", fn() => PricingTier::factory()->maximumQuantityEqualToMinimum()->create());
        $runTest("Pricing tier max quantity adjacent to min", fn() => PricingTier::factory()->maximumQuantityAdjacentToMinimum()->create());
        $runTest("Pricing tier max quantity wide range", fn() => PricingTier::factory()->maximumQuantityWideRange()->create());
        $runTest("Pricing tier negative price", fn() => PricingTier::factory()->negativePriceTier()->create());
        $runTest("Pricing tier free tier", fn() => PricingTier::factory()->freeTier()->create());
        $runTest("Pricing tier mid price tier", fn() => PricingTier::factory()->midPriceTier()->create());
        $runTest("Pricing tier max price tier", fn() => PricingTier::factory()->maxPriceTier()->create());
        $runTest("Pricing tier above max price tier", fn() => PricingTier::factory()->aboveMaxPriceTier()->create());

        $this->line("\n TESTING WISHLIST ITEM FACTORY");
        $runTest("Wishlist item default", fn() => WishlistItem::factory()->create());
        $runTest("Wishlist item customizable", fn() => WishlistItem::factory()->customizable()->create());

        $this->line("\n TESTING SAVED DESIGN FACTORY");
        $runTest("Saved design default", fn() => SavedDesign::factory()->create());
        $runTest("Saved design without preview", fn() => SavedDesign::factory()->withoutPreview()->create());
        $runTest("Saved design name empty", fn() => SavedDesign::factory()->designNameEmpty()->create());
        $runTest("Saved design name min length", fn() => SavedDesign::factory()->designNameMinLength()->create());
        $runTest("Saved design name mid length", fn() => SavedDesign::factory()->designNameMidLength()->create());
        $runTest("Saved design name max length", fn() => SavedDesign::factory()->designNameMaxLength()->create());
        $runTest("Saved design name too long", fn() => SavedDesign::factory()->designNameTooLong()->create());

        $this->line("\n TESTING SHOPPING CART FACTORY");
        $runTest("Shopping cart default", fn() => ShoppingCart::factory()->create());

        $this->line("\n TESTING CART ITEM FACTORY");
        $runTest("Cart item default", fn() => CartItem::factory()->create());
        $runTest("Cart item without preview", fn() => CartItem::factory()->withoutPreview()->create());
        $runTest("Cart item quantity below min", fn() => CartItem::factory()->quantityBelowMin()->create());
        $runTest("Cart item quantity at min", fn() => CartItem::factory()->quantityAtMin()->create());
        $runTest("Cart item quantity mid range", fn() => CartItem::factory()->quantityMidRange()->create());
        $runTest("Cart item quantity at max", fn() => CartItem::factory()->quantityAtMax()->create());
        $runTest("Cart item quantity above max", fn() => CartItem::factory()->quantityAboveMax()->create());

        $this->line("\n TESTING CUSTOMER ORDER FACTORY");
        $runTest("Customer order default", fn() => CustomerOrder::factory()->create());
        $runTest("Customer order processing", fn() => CustomerOrder::factory()->processing()->create());
        $runTest("Customer order ready for pickup", fn() => CustomerOrder::factory()->readyForPickup()->create());
        $runTest("Customer order completed", fn() => CustomerOrder::factory()->completed()->create());
        $runTest("Customer order cancelled", fn() => CustomerOrder::factory()->cancelled()->create());

        $staffForOrder = Staff::factory()->create();
        $runTest("Customer order assigned to staff", fn() => CustomerOrder::factory()->assignedTo($staffForOrder)->create());

        $runTest("Customer order phone too short", fn() => CustomerOrder::factory()->phoneTooShort()->create());
        $runTest("Customer order phone exact length", fn() => CustomerOrder::factory()->phoneExactLength()->create());
        $runTest("Customer order phone too long", fn() => CustomerOrder::factory()->phoneTooLong()->create());
        $runTest("Customer order name too short", fn() => CustomerOrder::factory()->customerNameTooShort()->create());
        $runTest("Customer order name min length", fn() => CustomerOrder::factory()->customerNameMinLength()->create());
        $runTest("Customer order name mid length", fn() => CustomerOrder::factory()->customerNameMidLength()->create());
        $runTest("Customer order name max length", fn() => CustomerOrder::factory()->customerNameMaxLength()->create());
        $runTest("Customer order name too long", fn() => CustomerOrder::factory()->customerNameTooLong()->create());
        $runTest("Customer order zero total", fn() => CustomerOrder::factory()->zeroTotal()->create());
        $runTest("Customer order minimal total", fn() => CustomerOrder::factory()->minimalTotal()->create());
        $runTest("Customer order typical total", fn() => CustomerOrder::factory()->typicalTotal()->create());

        $this->line("\n TESTING ORDER ITEM FACTORY");
        $runTest("Order item default", fn() => OrderItem::factory()->create());
        $runTest("Order item without preview", fn() => OrderItem::factory()->withoutPreview()->create());
        $runTest("Order item quantity below min", fn() => OrderItem::factory()->quantityBelowMin()->create());
        $runTest("Order item quantity at min", fn() => OrderItem::factory()->quantityAtMin()->create());
        $runTest("Order item quantity mid range", fn() => OrderItem::factory()->quantityMidRange()->create());
        $runTest("Order item quantity at max", fn() => OrderItem::factory()->quantityAtMax()->create());
        $runTest("Order item quantity above max", fn() => OrderItem::factory()->quantityAboveMax()->create());
        $runTest("Order item negative price", fn() => OrderItem::factory()->negativePriceItem()->create());
        $runTest("Order item free item", fn() => OrderItem::factory()->freeItem()->create());
        $runTest("Order item mid price item", fn() => OrderItem::factory()->midPriceItem()->create());
        $runTest("Order item with quantity 5", fn() => OrderItem::factory()->withQuantity(5)->create());

        $this->line("\n TESTING ORDER NOTE FACTORY");
        $runTest("Order note default", fn() => OrderNote::factory()->create());
        $runTest("Order note text empty", fn() => OrderNote::factory()->noteTextEmpty()->create());
        $runTest("Order note text min length", fn() => OrderNote::factory()->noteTextMinLength()->create());
        $runTest("Order note text mid length", fn() => OrderNote::factory()->noteTextMidLength()->create());
        $runTest("Order note text max length", fn() => OrderNote::factory()->noteTextMaxLength()->create());
        $runTest("Order note text too long", fn() => OrderNote::factory()->noteTextTooLong()->create());

        $this->line("\n TESTING CAROUSEL SLIDE FACTORY");
        $runTest("Carousel slide default", fn() => CarouselSlide::factory()->create());
        $runTest("Carousel slide with standard product", fn() => CarouselSlide::factory()->withStandardProduct()->create());
        $runTest("Carousel slide with customizable product", fn() => CarouselSlide::factory()->withCustomizableProduct()->create());
        $runTest("Carousel slide without product", fn() => CarouselSlide::factory()->withoutProduct()->create());
        $runTest("Carousel slide without image", fn() => CarouselSlide::factory()->withoutImage()->create());
        $runTest("Carousel slide title too short", fn() => CarouselSlide::factory()->titleTooShort()->create());
        $runTest("Carousel slide title min length", fn() => CarouselSlide::factory()->titleMinLength()->create());
        $runTest("Carousel slide title mid length", fn() => CarouselSlide::factory()->titleMidLength()->create());
        $runTest("Carousel slide title max length", fn() => CarouselSlide::factory()->titleMaxLength()->create());
        $runTest("Carousel slide title too long", fn() => CarouselSlide::factory()->titleTooLong()->create());
        $runTest("Carousel slide description min length", fn() => CarouselSlide::factory()->descriptionMinLength()->create());
        $runTest("Carousel slide description mid length", fn() => CarouselSlide::factory()->descriptionMidLength()->create());
        $runTest("Carousel slide description max length", fn() => CarouselSlide::factory()->descriptionMaxLength()->create());
        $runTest("Carousel slide description too long", fn() => CarouselSlide::factory()->descriptionTooLong()->create());
        $runTest("Carousel slide without description", fn() => CarouselSlide::factory()->withoutDescription()->create());

        $this->newLine();
        $this->line("========================");
        $this->info(" PASSED: $passed");
        $this->error(" FAILED: $failed");
        $this->line("========================");

        if ($failed > 0) {
            $this->error(" Some factory tests failed. Check the output above for error messages.");
        } else {
            $this->info(" All factory tests passed!");
        }

        $this->line("\n DETAILED RESULTS:");
        foreach ($results as $result) {
            $this->line($result);
        }

        return $failed === 0 ? Command::SUCCESS : Command::FAILURE;
    }
}