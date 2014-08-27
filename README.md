Magento Entity Generator
=========================

This Magento extension uses the library FAKER to generate entities with fake data for tests :

* Generate customers and their addresses with fake email, address, phone number, ...
* Automatically assign created customers to website, store and group
* Generate orders for registred customer using randomly :
    - Check and Bank Transfer as payment methods
    - Flat Rate as shipping method
* Depending on the module configuration in the backend, you can generate invoices and shipments for orders
* Generate categories and save them as children of :
    - A random existing category
    - An existing category specified by user
    - An existing category configured in System -> Configuration -> Entity Generator in Magento's backend
* Todo : Generate products (simple, configurable, downloadable, ...)

# I. Requirements

* Install PHP Composer (see bellow)
* Enable Check and Bank Transfer payment methods
* Enable Flat Rate shipping method
* Allow the use of symlinks in Magento Backend
To allow the use of symlinks, in your Magento's Backend go to :

**_System_** -> _**Configuration**_ -> _**Developer**_ -> _**Template Settings**_ -> "_**Allow Symlinks**_"

_There no security risk by allowing symlinks in Magento_

# II. Installation

### 1. Install PHP-Composer :
Download _**composer.phar**_ into your project :

`$ curl -sS https://getcomposer.org/installer | php`

_This will just check a few PHP settings and then download composer.phar to your working directory._

If you are not familiar with composer, please read the composer documentations on [getcomposer](https://getcomposer.org/) website

### 2. Create/Update your root composer.json : 

If you don't have a **_composer.json_** file, create it in your Magento root folder (or outside), otherwise just update yours, you can copy this one :

```json
{
    "name": "Your Project Name",
    "require": {
        "maverick/entity-generator": "*",
        "fzaninotto/faker": "*",
        "magento-hackathon/magento-composer-installer": "*"
    },
    "repositories": [
        {
            "type": "vcs",
            "url": "git://github.com/maverick193/entity-generator.git"
        },
        {
            "type": "vcs",
            "url": "git://github.com/maverick193/Faker.git"
        },
        {
            "type": "vcs",
            "url": "git://github.com/magento-hackathon/magento-composer-installer.git"
        }
    ],
    "extra":{
        "magento-root-dir": "htodcs/"
    }
}
```

* _**composer.json** and **composer.phar** should be in the same folder_
* Update the **_magento-root-dir_** node and specify your Magento root folder "_**web/**_", "_**./**_", ...
* The _**magento-composer-installer**_ will install the module via symlinks in your Magento folder structure, more information on [magento-composer-installer](https://github.com/magento-hackathon/magento-composer-installer)

### 3. Install Entity Generator via composer : 
`php composer.phar install`

That should install the extension and its dependencies in a folder named "vendor" and trigger modman to install the extension in your Magento folder structure.
Finally refresh Magento's cache and configure the extension in System -> Configuration -> Entity Generator


# III. Usage 

Entities can be generated :
- From backend (Maverick -> Entity Generator)
- Via Shell commands (generator.php located in shell folder)

All information about how to generate entities can be found on  [Entity Generator Wiki](https://github.com/maverick193/entity-generator/wiki/Magento-Entity-Generator-Wiki)

Compatibility
=============
Tested on Magento :
- Community >= 1.7
- Enterprise >= 1.12

Support and Contribution
========================
If you have any issues with this extension, please open an issue on Github.

Any contributions are highly appreciated. If you want to contribute, please open [pull request on GitHub](https://help.github.com/articles/using-pull-requests).

Copyright and License
=====================
License   : [OSL - Open Software Licence 3.0](http://opensource.org/licenses/osl-3.0.php).

Copyright : (c) 2013 Mohammed NAHHAS
