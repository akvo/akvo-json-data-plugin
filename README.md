The Akvo JSON Data Feed Plugin for WordPress.
=============================================

The data feed plugin provides an API and a Wordpress shortcode for
fetching items from a JSON formatted datafeed and caching them for a
specified interval.  If Curl is used as backend, also XML formatted
feeds are supported.

Data items are cached for a duration of twice the configured refetch
interval for the feed.  If the current item is older than the refetch
interval a refetch is attempted.  On failure the old item is returned.
The refetching frequency on failure is restricted to one attempt per
30s, unless the url changes on the feed.


Shortcode data_feed
-------------------

The shortcode can be used to extract, convert to a string and escape a value from the current data item in the feed.

        [data_feed name="my-example-data-feed" url="http://example.com/json-feed" interval="360" query="fieldname->subfield"]

The shortcode will be substituted with the selected value in the item, or an error message surrounded by tags `&lt;span class="data-feed-error"&gt;...&lt;span&gt;`.

### Parameters

* **name** is mandatory and specifies an identifier for the feed.
* **url** is optional if a feed with the specified name already have been declared and the feed handle exists in the database.  This is the url of the feed.  ** Note: **  due to a bug in Wordpress it is not possible to include characters encoded using html-entities in the url.
* **interval** is optional and specifies the caching interval in seconds.  If the current item have expired, a new item will be fetched on demand.  Defaults to the saved interval if the feed handle exists in the database, or to 24h if the handle doesn't exist.
* **query** is optional and can be used to select a particular field of the item.  The syntax is a list of fieldname or array indicies separated with the delimiter '->'.  Fieldnames are used verbatim, (i.e., spaces and other characters are included in the field name.).

Plugin architecture
-------------------

The plugin consists of three basic components:

* **feed cache** A chain of caches, with a network backend for fetching and caching items.
* **feed store** A persistant storage for *feed handles*.
* **object query language** A language for fetching data from a feed item.

Also there is an administrative interface for editing (overriding url and interval) and deleting feed handles.  (Not yet completed.)

PHP functions \DataFeed\DataFeed::item and \DataFeed\DataFeed::handle.
---------------------------------------------------------------------

The plugin API functions can be used to fetch feed items in skins and other templates.

	/**
	 * @param string $name     The name of the feed.
	 * @param string $url      The url of the feed.  If the feed handle already exists, the default will be whatever is stored in the database.
	 * @param int    $interval The fetch interval in seconds.  The default interval is 24h.
	 *
	 * @return FeedHandle which can be used to fetch the current item of the feed using the method getCurrentItem().
	 *
	 * @throws DataFeed\NonexistingFeedException if the url is omitted and the feed doesn't already exist.
	 */
	public static function handle( $name, $url = null, $interval = 86400 )


	/**
	 * @param string $name     The name of the feed.
	 * @param string $url      The url of the feed.  If the feed handle already exists, the default will be whatever is stored in the database.
	 * @param int    $interval The fetch interval in seconds.  The default interval is 24h.
	 *
	 * @return object The current item of the feed as an object.
	 * WARNING: the contents of the item is untrusted data, no
	 * validation or escaping has been made on the fields by the data
	 * feed plugin.
	 *
	 * @throws DataFeed\NonexistingFeedException if the url is omitted and the feed doesn't already exist.
	 */
	public static function item( $name, $url = null, $interval = 86400 )

