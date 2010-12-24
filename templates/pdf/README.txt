Tips for avoiding failures like blank screens

The TCPDF library can become resource-intensive for large projects, and depending on the version and settings for PHP on your server. Here are some things to try if you experience output problems.

1. Include page breaks between parts and items.
Page breaks seem to also help to break up memory usage. The checkboxes on the PDF output screens let you choose to break up the parts and items. Try putting in more page breaks if exports are failing.

2. Combine items in the Project editing screen
We have seen improved results with merging individual items into one on the Project editing screen.

3. (Advanced) Change the max_execution_time and/or memory_limit settings
This is a little riskier in a hosted environment, as it attempts to override the default settings for PHP that the hosting company has set. You might want to consult with your hosting company before modifying these settings.

Near the top of the templates/pdf/base.php file, you will see these lines:
//ini_set('max_execution_time', '30');
//ini_set('memory_limit', '128M');

You could try uncommenting one or the other line, and adjusting the settings.
max_execution_time is measured in seconds. default is 30
memory_limit is usually in megabytes (M). 128M is the default for very recent versions of PHP, but your hosting company might have different settings.

Changing those settings has a good chance of avoiding a blank screen on the effort to export, but is subject to a variety of server settings. Results may vary.


Note on CJK fonts:
Unfortunately, using Chinese, Japanese, and Korean fonts are more resource intensive than western fonts. This may also affect results.

If you are using Adobe PDF Reader, please also double check that you have the required extensions to it installed.

 