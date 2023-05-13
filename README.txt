=== SheetsCell ===

Contributors: mdashikul
Tags: google sheets, shortcode, api key, cell data
Requires at least: 5.2
Tested up to: 6.2
Stable tag: 1.0.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.txt

A simple plugin that helps you display specific cell data from a Google Sheet on your WordPress website using shortcodes. Just add your Google API key and Google Sheets ID, and use the shortcode in your pages and posts.

== Description ==

SheetsCell is a lightweight and easy-to-use plugin that lets you display specific data from a Google Sheet on your WordPress website. Simply add your Google API key and Google Sheets ID to the plugin settings, and use the shortcode in your pages and posts to display the data you want.

== Features ==

* Display specific cell data from a Google Sheet on your WordPress website using shortcodes
* Easy-to-use plugin settings page to add your Google API key and Google Sheets ID
* Lightweight and optimized for performance

== Shortcode ==

* [sheets_cell name="" cell_id=""]
* cell_id: Google Spreedsheet cell id ( Sheet1!A1 ) = Sheet1 = 'set your sheet name' - A1 = 'cell id'
* name: Add name for the shortcode ( price )
 
= Installation =
How to install SheetsCell
1. Upload the sheetscell folder to the /wp-content/plugins/ directory or Navigate to the 'Add New' in the plugins dashboard
and Search for 'SheetsCell'
2.Activate the plugin through the 'Plugins' menu in WordPress
3.SheetsCell Settings panel is on the wordpress Settings submenu 
4.Go to the SheetsCell settings page and add your Google API key and Google Sheets ID
Use the shortcode [sheets_cell cell_id="Your cell id"] ( [sheets_cell cell_id="Sheet1!A1"] )  in your pages and posts to display your Google Sheet data

== Frequently Asked Questions ==

= How do I get a Google API key? =

You can follow the instructions in Google's documentation to get an API key: https://console.cloud.google.com/

= How do I find my Google Sheets ID? =

You can find your Google Sheets ID in the URL of your Google Sheet. It's the string of letters and numbers between "/d/" and "/edit" in the URL.

= How do I find sheets Cell ID? =
Right click on the cell, Scroll down hover over more view more cell actions, click protect range( click on add a sheet or range ) under range you can see the sheet id

== Screenshots ==

01. Sheetscell settings panel
02. Shortcode used in table
03. Data disply to frontend
04. Google Sheets data view
05. Create project to google console
06. Select the project and hover over APIs & Services, and credentials
07. Create API key
08. genarated Google API key
09. Search Google Sheets API in APIs and Services
10. Enable Google Sheets API for the project
11. Make your google sheets permission for anyone with the link
12. Get Sheets ID 
13. Get Sheets cell ID

== Changelog ==

= 1.0.1 =

Initial release
