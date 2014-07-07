Joomla Platform Examples
========================

These examples are provided to help you learn how to structure and build applications with the Joomla Platform from the cli side.

Installation
============

install like any joomla extension (todo pkg, manifest)

Running Examples
================

All the examples in the `cli` folder are run from the command line or terminal.
All you need is PHP configured to run from the command line.



Command Line Applications
=========================

The examples found in the `cli` folder are all based on the new `JApplicationCli` class.
The is a base level class purpose built for running applications from the command line.

Download and Unzip Joomla Package
---------------

This is a simple example application that download and unzip a Joomla Package .

Download and Unzip Joomla! from CLI
---------------

**Usage:**

    php downloadcli.php
    

1. Option: 

        -z [filezip]

    *Example usage:*
    
        php downloadcli.php -z joomla.zip
    
    Unzip the joomla package from joomla.zip

2. Option: 

        -u [url]

    *Example usage:*
  
        php downloadcli.php -u http://joomlacode.org/gf/download/frsrelease/19239/158104/Joomla_3.2.3-Stable-Full_Package.zip
    
    Download and unzip from http://joomlacode.org/gf/download/frsrelease/19239/158104/Joomla_3.2.3-Stable-Full_Package.zip

3. Option: 

        -f [file]

    *Example usage:*
  
        php downloadcli.php -f joomlacode.txt

    Download and unzip from url listed on file joomlacode.txt

Install Joomla Package
----

This application install a Joomla Package.

#### Usage

    php icli.php --db-user=root --db-name=t323 --db-pass=1234 --admin-user=admin --admin-pass=admin --admin-email='admin@example.com'

Install Joomla extensions
---------

This application install joomla extensions. 

#### Usage: 

    php installcli.php [options]

1. **Option:** 
    
        -f [extensionfile]

    **Example usage:** 
    
        php installcli.php -f plg_example.zip

    Install the example plugin from /tmp/plg_example.zip

2. **Option:**

        -u [extensionurl]  
    
    **Example usage:**
    
        php installcli.php -u jfiles.csv
    
    Install the extensions listed on jfiles.csv from web

3. **Option:**

        -m

    **Example usage:**
    
        php installcli.php -m
    
    Install the extensions listed on /cli/files.txt

4. **Option:**

        -w [extensionurl]
    
    **Example usage:**
    
        php installcli.php -w http://www.joomladdons.eu/update/mod_related_author_update.xml

    Install the extension module from www.joomladdons.eu/update/mod_related_author_update.xml


Overload cli
------------

Based on the com_overlaod create dummy articles and category

**Usage:**

    php overloadcli.php [options]

**Example usage: **

    php overloadcli.php -a 10 -c 10 -d 1

Create 10 articles from 10 categories with depth=1 ie create 100 dummy articles
