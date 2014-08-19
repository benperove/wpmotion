=== WP Motion ===
Contributors: bperove
Donate link: http://goo.gl/jqzNs6
Tags: migration, migrate, transfer, move
Requires at least: 3.2
Tested up to: 3.9.2
Stable tag: 0.9.4
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

WP Motion automates all aspects of WordPress migrations between hosting providers, enabling true hands-off migrations.

== Description ==

WP Motion will migrate your WordPress site with zero interaction, from start to finish. Simply input the credentials for you hosting accounts and WP Motion does the rest.

When it comes to migrations, WordPress site owners are faced with many options. While other migration plugins exist, WP Motion is the **only** plugin to offer server-assisted migrations which automate the entire process. In addition to static files and databases, WP Motion can also migrate SSL certificates and automate DNS, enabling true one-click migration.

Currently supported migration paths:

* Bluehost ➙ WP Engine

An inside look (note the payment form has since been removed):

https://www.youtube.com/watch?v=pJP8lKccrd8

WP Motion is hosted at [Github](https://github.com/benperove/wpmotion). I do not monitor WP forums, so use ben(at)wpmotion.co for support questions.

== Installation ==

= Using The WordPress Dashboard =

1. Navigate to the 'Add New' in the plugins dashboard
2. Search for 'WP Motion'
3. Click 'Install Now'
4. Activate the plugin on the Plugin dashboard

= Uploading in WordPress Dashboard =

1. Navigate to the 'Add New' in the plugins dashboard
2. Navigate to the 'Upload' area
3. Select wpmotion.zip from your computer
4. Click 'Install Now'
5. Activate the plugin in the Plugin dashboard 

= Using FTP =

1. Download wpmotion.zip
2. Extract the wpmotion directory to your computer
3. Upload the wpmotion directory to the /wp-content/plugins/ directory
4. Activate the plugin in the Plugin dashboard

== Frequently Asked Questions ==

= Is WP Motion secure? =
WP Motion is secured by 256-bit SSL encryption and does not accept unencrypted HTTP requests. All passwords are salted and hashed using the mcrypt library (256-bit AES) before being stored in the database. 
The encryption key is held securely on a remote server. WP Motion requires your hosting credentials. For your increased privacy, you may wish to temporarily change your hosting provider’s password before the migration. You can then change your password back once the migration is done. 

Following the migration, all hosting credentials and site contents are purged from our servers.

= Is WP Motion reliable? =
WP Motion is very reliable. It has been responsible for a large number of migrations. 

Switching IP addresses can be tricky. WP Motion has a number of mechanisms in place to safeguard against problems at this critical point. These mechanisms check to make sure that a list of conditions are met, prior to even considering the migration “ready for DNS changes.” WP Motion compares the new site with the original and will only switch DNS if they are greater than 97% similar.

= What happens to my DNS after the migration? =
As part of the migration process, DNS is moved to WP Motion's infrastructure. Following the migration, DNS will be changed back to your original name servers, typically within the hour. At this point, DNS is under control by the original web host. 

However, you have the option to leave DNS with WP Motion once the migration is complete, as some customers may wish to have their accounts terminated with the previous host.

= My database is massive. Can it still be migrated? =
Yes. During beta tests, we were able to consistently move a very large database.

The stats for our test database:

* DB rows: 1.53m <-- that's a lot of comments!
* DB size: 1070MB
* DB size compressed: 43.9MB
* Compression ratio: 24:1
* WP Engine max file size: 512MB
* Theoretical max size before compression: 12.4TB

= Will additional paths be supported? =
Definitely. Please vote for the hosts you would like [migration support](https://wpmotion.co/supported-hosts) for.

== Upgrade Notice ==

None yet.

== Screenshots ==

New screenshots to be added shortly.

== Changelog ==

= 0.9.4 =
* Initial source drop
