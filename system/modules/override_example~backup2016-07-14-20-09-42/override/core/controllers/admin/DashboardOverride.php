<?php

/**
 * @package Override module
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace modules\override_example\override\core\controllers\admin;

use core\controllers\admin\Dashboard;
use core\models\Price as ModelsPrice;
use core\models\Order as ModelsOrder;
use core\models\Report as ModelsReport;
use core\models\Review as ModelsReview;
use core\models\Product as ModelsProduct;
use core\models\Analytics as ModelsAnalytics;

/**
 * Overrides dashboard controller
 */
class DashboardOverride extends Dashboard
{

    /**
     * Constructor
     * @param ModelsProduct $product
     * @param ModelsPrice $price
     * @param ModelsOrder $order
     * @param ModelsReport $report
     * @param ModelsAnalytics $analytics
     * @param ModelsReview $review
     */
    public function __construct(ModelsProduct $product, ModelsPrice $price, ModelsOrder $order, ModelsReport $report, ModelsAnalytics $analytics, ModelsReview $review)
    {
        parent::__construct($product, $price, $order, $report, $analytics, $review);
    }

    /**
     * Adds a message to the dashboard controller
     */
    public function dashboard()
    {
        $this->setMessage('Hello! This test message came from modules\override\override\core\controllers\admin\DashboardOverride::dashboard()');
        parent::dashboard();
    }
}
