<?php

namespace Islandora\ImageService\Provider;

use Silex\Application;
use Silex\ServiceProviderInterface;
use Silex\ControllerProviderInterface;
use Islandora\Chullo\FedoraApi;
use Islandora\Chullo\TriplestoreClient;
use Islandora\Chullo\Uuid\UuidGenerator;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Yaml\Yaml;
use Islandora\CollectionService\Controller\CollectionController;

class ImageServiceProvider implements ServiceProviderInterface, ControllerProviderInterface
{
  /**
   * Part of ServiceProviderInterface
   */
  function register(Application $app) {
    //
    // Define controller services
    //
    //This is the base path for the application. Used to change the location
    //of yaml config files when registerd somewhere else
    $app['islandora.BasePath'] = __DIR__.'/..';
    
    // If nobody registered a UuidGenerator first?
    if (!isset($app['UuidGenerator'])) {
      $app['UuidGenerator'] = $app->share($app->share(function() use ($app) {
        return new UuidGenerator();
      }));
    }
    $app['islandora.imagecontroller'] = $app->share(function() use ($app) {
      return new \Islandora\ImageService\Controller\ImageController($app, $app['UuidGenerator']);
    });
    if (!isset($app['twig'])) {
      $app['twig'] = $app->share($app->extend('twig', function($twig, $app) {
        return $twig;
      }));
    }
    if (!isset($app['api'])) {
      $app['api'] =  $app->share(function() use ($app) {
        return FedoraApi::create($app['config']['islandora']['fedoraProtocol'].'://'.$app['config']['islandora']['fedoraHost'].$app['config']['islandora']['fedoraPath']);
      });
    }  
    if (!isset($app['triplestore'])) {
      $app['triplestore'] = $app->share(function() use ($app) {
        return TriplestoreClient::create($app['config']['islandora']['tripleProtocol'].'://'.$app['config']['islandora']['tripleHost'].$app['config']['islandora']['triplePath']);
      });
    }
    /**
    * Ultra simplistic YAML settings loader.
    */
    if (!isset($app['config'])) {
      $app['config'] = $app->share(function() use ($app){
         {
          if ($app['debug']) {
            $configFile = $app['islandora.BasePath'].'/../config/settings.dev.yml';
          }
          else {
            $configFile = $app['islandora.BasePath'].'/../config/settings.yml';
          }
        }    
        $settings = Yaml::parse(file_get_contents($configFile));
        return $settings;
      });
    }
  }

  function boot(Application $app) {
  }

  /**
   * Part of ControllerProviderInterface
   */
  public function connect(Application $app) {
    $CollectionControllers = $app['controllers_factory'];
    //
    // Define routing referring to controller services
    //

    $ImageControllers->post("/image/{id}", "islandora.imagecontroller:create")
      ->value('id',"")
      ->bind('islandora.imageCreate');
    $ImageControllers->post("/image/{id}/files/{file}", "islandora.imagecontroller:addImage")
      ->bind('islandora.imageAddImage');
    return $ImageControllers;
  }
}