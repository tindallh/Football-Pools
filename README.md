# Football-Pools<br>
Sources for the Football-pools.org Website,<br><br>
Football Pool module for PHP-Nuke and associated files.<br>
Some files may be originally included in PHP-Nuke, included here for dependencies or having been modified for a specific reason, as in the case of the "Your_account" module.<br>
Football Pools module written by: Henry B. Tindall, Jr.<br>
Current version 19.01<br>
first written: 15 Aug 2004<br>
last modified: 23 Sep 2019<br>
<br>
Current plan is to re-write the entire conglomeration of scripts after re-creating the database in a more sensible, normalized manner.  At this point the CMS of choice is looking to be Drupal, but open to suggestion with appropriate rationale.<br>
<pre>
 Version 19.01 - 23 Sep 2019 - fixed pop-up schedule error for Neutral
                 site.
         18.01 - 11 Sep 2018 - new versioning to coincide with season,
                 modded to account for ties in NFL teams records.
          9.02 - 25 Aug 2016 - cleaning up TV networks, moving to popup
          9.01 - 22 Aug 2016 - adding logic to account for NFL teams
                 moving cities, started cleanup to move to Drupal, backing
                 out aberrant Moneyline mods, fixed TeamSchedule links
          8.01 - 16 Sep 2015 - Started Moneyline mods.
          7.03 - 13 Nov 2013 - Added the overlib routines to do the
                 schedule popups on the "MakePicks" page.
          7.02 - 11 Nov 2013 - Added logic to do the Timezone conversion
                 from MT to CT.
          7.01 - 06 Oct 2012 - Fixed the graph problem, using jpgraph 2.2
                 and moving around some fonts.
          7.00 - 17 Sep 2012 - Added logic for multiple TV networks.
          6.02 - 26 Nov 2010 - Added tooltips with team names and mascots
                 on the "show all picks" page for the team logos.
          6.01 - 26 Nov 2010 - New Graphs, for the overall picker rankings
                 over time.  Also fixed '@'/'vs.' issue in schedules.
          5.05 - 10 Dec 2009 - Changed font styles
          5.04 - 22 Nov 2009 - Added Helmet .gif, team name and mascot to
                 the Team schedules page, added button for team schedules,
                 and added the Mascot table to the database...
          5.03 - 04 Sep 2009 - Finally got rid of those annoying zeros
                 in front of the percentages, even in the Graphs !!!
          5.02 - 03 Sep 2009 - one missed weekID v. sweekID, fixed DST
                 issue.
          5.01 - 23 Aug 2009 - Changed navigation menu from text to
                 buttons, cleaned up.
          5.00 - 20 Aug 2009 - Bug quashing - mainly the sweekId vs.
                 weekid problem.
          4.08 - 03 Dec 2008 - added code to eliminate the 0-0 records for
                 the 900-series team_ids.
          4.07 - 14 Nov 2008 - fixed the background in the "make my picks"
                 page when there is a completed game not picked after a
                 game that was picked.  Also corrected a display error
                 where rankings weren't displayed for games that had no
                 spreads posted.
          4.06 - 06 Nov 2008 - fixed an oversight where the match against
                 the required percentage was ">" instead of ">=".
          4.05 - 28 Sep 2008 - added code to display pool name in graph,
                 added logic for proper grammar (possessive of names
                 ending in "s").
          4.04 - 10 Nov 2007 - fixed a date/time problem.
          4.03 - 03 Oct 2007 - added percentages picked to top of "Make my
                 picks" page and fixed the no TV network logo if there
                 were no spreads for a game in the spreads pool.
          4.02 - 02 Sep 2007 - added logic in WinnersWeek to make the
                 percentage of games picked come out correctly when in a
                 spreads Pool.
          4.01 - 21 Aug 2007 - added 'testing' variable and 'top25' to the
                 config file.  Some other cosmetic changes
          4.00 - 29 Jul 2007 - New season, new version.  Changed the way
                 the pool memberships were determined.  Added the ability
                 to override the actual date in the config.inc.php file
                 for testing and such.
          3.03 - 29 Dec 2006 - added the "neutral site" flag to display
                 "vs." instead of "at" for bowl games, etc. This will make
                 the home and away records accurate.
          3.02 - 11 Nov 2006 - Added rankings to the team schedules.
          3.01 - 29 Aug 2006 - Added the "poolname" variable to
                 distinguish between multiple pools by using more than one
                 table of picks, I.E. "nuke_pool_picks_private1", and
                 moved the pool-specific variables into a config.inc.php.
                 Added the $poolname variable to the page headers.
          3.00 - 17 Aug 2006 - New season, new version.  From here on out,
                 each new season will start a new Major version.
                 This year we start with adding a 'none' pick box and the
                 Station icon if the game is televised.
          2.26 - 11 Jan 2006 - found one more sql statement that needed
                 to have the league added, and made some changes to box
                 titles and headers to make it obvious which pool the user
                 is in.
          2.25 - 21 Nov 2005 - Added links on the TeamSchedule page to see
                 the opponents' schedules.
          2.24 - 04 Nov 2005 - fixed the number of games showed in the
                 overall leaderboard title line.
          2.23 - 02 Nov 2005 - finally fixed the "next game is at:" stuff
          2.22 - 01 Nov 2005 - added rankings to the weekly winners page
                 and added logic to make it easier to change the minimum
                 number of games picked to be in the stats.
          2.21 - 30 Oct 2005 - changed the graphs a bit.
          2.20 - 18 Oct 2005 - Added schedules page and records on picks
                 page.
          2.14 - 06 Oct 2005 - Changed background display in MakePicks for
                 completed games to make more pleasant and W3C compliant.
          2.13 - 26 Sep 2005 - Made all picks display more W3C compliant.
          2.12 - 18 Sep 2005 - Changed output for "Show all picks".
          2.11 - 09 Sep 2005 - Display CDT or CST for the game times, to
                 prevent confusion.
          2.10 - 08 Sep 2005 - Added code to enable pools with or without
                 spreads.
          2.01 - 05 Sep 2005 - fixed the graphing code and the WinnersAll
                 module errors.
          2.00 - 06 Jun 2005 - added "season" and "league" fields to
                 tables and scripts to enable different types of pools
                 over multiple years.
          1.01 - 28 Nov 2004 - added performance graphs
          1.00 - 15 Aug 2004 - Initial Release
</pre>
