<?php

/**
 * Cache interface for feed items.
 *
 * PHP version 5.3
 *
 * @category   Wordpress plugin
 * @package    Data Feed
 * @author     Andreas Jonsson <andreas.jonsson@kreablo.se>
 * @copyright  2015 Andreas Jonsson
 * @license    GNU AFFERO GENERAL PUBLIC LICENSE version 3 http://www.gnu.org/licenses/agpl.html
 * @version    Git: $Id$
 * @link       https://github.com/akvo/akvo-json-data-plugin
 * @since      File available since Release 1.0
 */

namespace DataFeed\Cache;

/**
 * Store interface for data feed handles.
 */
interface FeedItem
{

	function getCurrentItem($feedName, $url, $interval, $format);

}