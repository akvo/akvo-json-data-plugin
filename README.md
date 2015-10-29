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

The shortcode will be substituted with the selected value in the item, or an error message surrounded by tags `<span class="data-feed-error">...</span>`.

### Parameters

* **name** is mandatory and specifies an identifier for the feed.
* **url** is optional if a feed with the specified name already have been declared and the feed handle exists in the database.  This is the url of the feed.  ** Note: **  due to a bug in Wordpress it is not possible to include ampersand characters encoded as `&#038;` in the url.  It should work to use `&amp;` though.
* **interval** is optional and specifies the caching interval in seconds.  If the current item have expired, a new item will be fetched on demand.  Defaults to the saved interval if the feed handle exists in the database, or to 24h if the handle doesn't exist.
* **query** is optional and can be used to select a particular field of the item.  The syntax is a list of fieldname or array indicies separated with the delimiter '->'.  Fieldnames are used verbatim, (i.e., spaces and other characters are included in the field name.).
* **pagination_policy** is optional and can be used to set the pagination policy on the feed.  (See below.)

Plugin architecture
-------------------

The plugin consists of three basic components:

* **feed cache** A chain of caches, with a network backend for fetching and caching items.
* **feed store** A persistant storage for *feed handles*.
* **object query language** A language for fetching data from a feed item.

Also there is an administrative interface for editing (overriding url and interval) and deleting feed handles.  (Not yet completed.)

Pagination support and the merging feed cache
---------------------------------------------

A feed can have its items divided into *pages* where each page is fetched from a separate *sub feed* which have its own URL.

The merging feed supports transparently fetching pages and merging them into one item.  The merging feed cache is enabled by specifying a *pagination policy*.

The merging feed cache uses the following components:

* **Page url resolver** is a component that generates the URL to the
    next page, given the previous page, the URL of the main feed, and
    the page number.  The default implementation - 'next' - obtains
    the URL from a field with the name 'next' in each page.  The field
    name is configurable via the pagination policy.  Currently
    available implementations: 'next', 'null'.
* **Page update checker** is a component that determines which pages
    have been updated recently and needs to be refetched.  The default
    implementation - 'version-array' - obtains an array of *versions*
    from the first page in the item.  The field is by default named
    'page_versions' and is configurable via the pagination policy.
    Currently available implementations: 'version-array', 'null'.
* **Object merger** is a component that is used to merge the pages
    into one page.  This is currently not configurable as the default
    implementation is expected to cover most use cases.

### Pagination policy

The pagination policy is specified using the following syntax:

    page-url=<page url component>[:<parameter>]&page-update-check=<page update check component>[:<parameter>][&limit=<postive integer>]

For example it can be passed as a parameter to the short code:

    [data_feed name="my-example-data-feed" url="http://example.com/json-feed" interval="360"
               pagination_policy="page-url=next:next-page-url&page-update-check=null&limit=20"]

In this example the page url resolver is set to 'next' with the field
name 'next-page-url' and the page update checker is set to 'null'
which always indicates that all pages are updated.

The page URL of the first page is taken from the 'url' parameter and
of subsequent pages the page url is taken from the field
'next-page-url' of the preceeding page.  The last page is the page
where 'next-page-url' is missing or is empty.  An exception will be
thrown if the page URL's form a loop.

The 'null' page update checker always indicates that all pages have
been updated, which effectively forces a refetch of all pages every
time the main item is refetched.

The limit parameter sets a limit on the number of pages that will be
fetched and merged.

The pagination policy can be overridden in the administration
interface.


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
	public static function handle( $name, $url = null, $interval = 86400, $pagination_policy = null )


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
	public static function item( $name, $url = null, $interval = 86400, $pagination_policy = null )

Widget
------

The plugin bundles a widget which can be used for presenting data feed
items from which title, time, text, link url and thumbnail image url
can be extracted from.  See the widget configuration under appearance
in the Wordpress administration dashboard.

Upgrade
-------

Inactivate the plugin before upgrading, to allow database schema updates to take effect.
