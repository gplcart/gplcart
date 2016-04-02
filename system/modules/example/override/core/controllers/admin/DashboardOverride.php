<?php

/**
 * @package GPL Cart core
 * @version $Id$
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html
 */

namespace modules\example\override\core\controllers\admin;

use core\controllers\admin\Dashboard;
use core\models\Product;
use core\models\Price;
use core\models\Order;
use core\models\Report;
use core\models\Analytics;
use core\models\Review;

class DashboardOverride extends Dashboard
{

    /**
     * Constructor
     * @param Product $product
     * @param Price $price
     * @param Order $order
     * @param Report $report
     * @param Analytics $analytics
     * @param Review $review
     */
    public function __construct(Product $product, Price $price, Order $order, Report $report, Analytics $analytics, Review $review)
    {
        parent::__construct($product, $price, $order, $report, $analytics, $review);
    }

    /**
     * Adds a message to the dashboard controller
     */
    public function dashboard()
    {
        $this->setMessage('Hello! This test message came from modules\example\override\core\controllers\admin\DashboardOverride::dashboard()');
        parent::dashboard();
    }
}
