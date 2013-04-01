Sf1SecurityBundle
=================

This bundle allows to reuse Symfony 1.4 authenthicated session in Symfony2 projects. I needed to smoothly migrate from old Sf14 while keeping existing application and using the same database.

Sf1 sfBasicSecurityUser implementation sets session variable:

    $this->setAttribute('username', $your_user_name);

after successful login. This variable is read from database by sf2 authenthication provider and used to look up user from UserProvider. The assumpton is that sessions are kept in database and users table is at leas minimally compatible (I use FosUserBundle).
I needed to copy PdoSessionHandler since original one did base64 encode/decode when writing to database, which broke sf1 sessions. Tested on Symfony 2.2 only, status is "works for me", and I hope this will be useful to someone.

To install you need to clone this to src/Dsnet/Sf1SecurityBundle/ , add:

            new Dsnet\Sf1SecurityBundle\DsnetSf1SecurityBundle(),
            
to your AppKernel.php and configure PdoSessionStorage according to: http://symfony.com/doc/current/cookbook/configuration/pdo_session_storage.html

config.yml:

    services:
        pdo:
            class: PDO
            arguments:
                - "pgsql:host=%database_host%;port=%database_port%;dbname=%database_name%"
                - %database_user%
                - %database_password%

        session.handler.pdo:
            class:     Dsnet\Sf1SecurityBundle\Session\Handler\PdoSessionHandler
            arguments: [@pdo, %pdo.db_options%]

parameters.yml:

    pdo.db_options:
        db_table:    sessions
        db_id_col:   sess_id
        db_data_col: sess_data
        db_time_col: sess_time
        
I use lighttpd, and in order to integrate both apps I used following configuration:

    alias.url  = (
       "/sf/" => "SF1/lib/vendor/symfony/data/web/sf/",
       "/noclegi/" => "SF2/web/"
    )
    
    
    url.rewrite-once = (    
    ...
    "^/noclegi/bundles/.+" => "$0",
    "^/noclegi/(.*)" => "/noclegi/app_dev.php/$1",
  
Lastly, you need to make sure css/js files are available to both applications:

    $HTTP["url"] =~ "/css/" {
        url.rewrite-if-not-file = (
          "^(.*)" => "/noclegi/css/$1"
        )
    }
    $HTTP["url"] =~ "/js/" {
        url.rewrite-if-not-file = (
          "^(.*)" => "/noclegi/js/$1"
        )
    }
