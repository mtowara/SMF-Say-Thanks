Say Thanks provides the ability for users to thank posts.

[size=14pt]Requirements[/size]

This mod requires PHP 5.3 or above.

Postgresql users: this mod requires v9+ for the stats page

[b]Core-based theme users[/b]

If you find that the stats page test fails on installation, you are likely to be using a core-based theme. The reason it fails is because the stats template is completely different to the one in the default theme. The mod supports the core theme by targeting it specifically but it cannot tell if the theme you are using is based on core. You can still go ahead and install the mod and it will work fine with the caveat that the stats page won't have the thanks stats and the buttons will look a bit out of place because of the styling. To add support to your theme simply follow these instructions, for this example lets say you are using Back-n-Black theme:
[list type=decimal]
[li]Install the mod as usual making sure to install in core and your custom theme, it will warn you about your theme that failed the test, you can continue[/li]
[li]Rename the [b]Stats.template.php[/b] in \Themes\backnblack_204 to something else for example Stats.template.backup.php[/li]
[li]Copy the [b]Stats.template.php[/b] and [b]SayThanks.template.php[/b] from \Themes\core into \Themes\backnblack_204[/li]
[li]Copy the Say Thanks style sheet from \Themes\core\css\saythanks.css and place over the styling in \Themes\backnblack_204\css\ (this step is optional, without this change the buttons look a bit thicker because the icons are doubled up but still look ok)[/li]
[/list]

Now the stats page and little icons next to the Say Thanks links should be working. You just need to make sure you do the reverse of the above steps when you come to uninstall.

[size=14pt]Additional Notes and known limitations[/size]

[list]
[li]Posts that require users to thank will not show content on reply page or the posts by user page even if the user has thanked the post already[/li]
[/list]

[size=14pt]Change Log[/size]

[b]1.3.6[/b]
[list]
[li]Thanked posts page now linked on profile[/li]
[/list]

[b]1.3.5[/b]
[list]
[li]Resolved conflict with Display Additional Membergroups[/li]
[/list]

[b]1.3.4[/b]
[list]
[li]Fixed Russian issue with json[/li]
[li]Added Russian UTF-8[/li]
[li]Added Norwegian translation by Svendsen[/li]
[li]Added Spanish translation by d3vcho[/li]
[/list]

[b]1.3.3[/b]
[list]
[li]Fixed stats page bug introduced in previous version[/li]
[li]Croatian translation by anonymous[/li]
[li]Russian translation by Sergey[/li]
[li]Persian translation by roza[/li]
[/list]

[b]1.3.2[/b]
[list]
[li]Updated german translation, thanks to larry007[/li]
[li]Replaced Top 10 Thankers with Top 10 Thanked posts[/li]
[li]Added thanked by user posts page[/li]
[/list]

[b]1.3.1[/b]
[list]
[li]Resolved conflict with quick edit button[/li]
[/list]

[b]1.3[/b]
[list]
[li]CSS now in standalone file to help those of you with index.css variations, also makes uninstalling core-based themes easier[/li]
[li]Fixed minor undeclared variable warning[/li]
[li]Resolved conflict with one of the Google Analytic mods[/li]
[li]Hide content plugin for show content on thank[/li]
[li]Ajax updates thankers list[/li]
[li]IE 8 support for ajax button[/li]
[/list]

[b]1.2.2[/b]
[list]
[li]Improved browser support for ajax button[/li]
[/list]

[b]1.2.1[/b]
[list]
[li]Ajax enabled for thank button[/li]
[li]Stats page supports Core theme[/li]
[li]Thanking enabled for locked topics[/li]
[/list]

[b]1.2[/b]
[list]
[li]Ability to disable on certain boards[/li]
[li]Hide content plugin support[/li]
[li]Fixed issue with locked posts[/li]
[/list]

[b]1.1.2[/b]
[list]
[li]German translation[/li]
[/list]

[b]1.1.1[/b]
[list]
[li]Fixed default value check for new configuration settings[/li]
[li]Italian complete translation[/li]
[/list]

[b]1.1[/b]
[list]
[li]Show thanked posts list to view all your posts that have been thanked[/li]
[li]Top ten thanked users and top ten thankers on stats page[/li]
[li]Withdraw thanks function[/li]
[li]Admin options to hide thanks count and above features[/li]
[/list]

[b]1.0-1.0.2[/b]
[list]
[li]Draft release[/li]
[/list]

[size=14pt]Translation Credits[/size]
Croatian - ameo
Italian - Ninja ZX-10RR
German - larry007
Russian - Sergey
Persian - roza
Norwegian - Svendsen
Spanish - d3vcho

Icon courtesy of Yusuke Kamiyamane (http://www.iconfinder.com/icondetails/27245/24/fine_good_hand_ok_thumb_thumbs_up_up_icon)
Tick and cross icon courtesy of Pixel Mixer (https://www.iconfinder.com/iconsets/basicset)
Loading icon courtesy of Preloaders.net

[size=14pt]Support[/size]
Support for this modification can be found in the support topic.

[hr]
[url=http://creativecommons.org/licenses/by/3.0][img]http://i.creativecommons.org/l/by/3.0/80x15.png[/img][/url]
This work is licensed under a [url=http://creativecommons.org/licenses/by/3.0]Creative Commons Attribution 3.0 Unported License[/url]