<?php

namespace Islandora\ImageService\Controller;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Islandora\Chullo\Uuid\IUuidGenerator;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class ImageController {

    protected $uuidGenerator;

    public function __construct(Application $app, IUuidGenerator $uuidGenerator) {
        $this->uuidGenerator = $uuidGenerator;
    }

    public function create(Application $app, Request $request, $id) {
        $tx = $request->query->get('tx', "");
        //Check for format
        $format = NULL;
        try {
          $format = \EasyRdf_Format::getFormat($contentType = $request->headers->get('Content-Type', 'text/turtle'));
        } catch (\EasyRdf_Exception $e) {
          $app->abort(415, $e->getMessage());
        }

        //Now check if body can be parsed in that format
        if ($format) { //EasyRdf_Format
          //@see http://www.w3.org/2011/rdfa-context/rdfa-1.1 for defaults
          \EasyRdf_Namespace::set('pcdm', 'http://pcdm.org/models#');
          \EasyRdf_Namespace::set('nfo', 'http://www.semanticdesktop.org/ontologies/2007/03/22/nfo/v1.2/');
          \EasyRdf_Namespace::set('isl', 'http://www.islandora.ca/ontologies/2016/02/28/isl/v1.0/');
          \EasyRdf_Namespace::set('ldp', 'http://www.w3.org/ns/ldp');

          //Fake IRI, default LDP one for current resource "<>" is not a valid IRI!
          $fakeUuid = $this->uuidGenerator->generateV5("derp");
          $fakeIri = new \EasyRdf_ParsedUri('urn:uuid:' . $fakeUuid);

          $graph = new \EasyRdf_Graph();
          try {
            $graph->parse($request->getContent(), $format->getName(), $fakeIri);
          } catch (\EasyRdf_Exception $e) {
            $app->abort(415, $e->getMessage());
          }
          //Add a pcmd:object type
          $graph->resource($fakeIri, 'pcdm:Object');

          //Check if we got an UUID inside posted RDF. We won't validate it here because it's the caller responsability
          if (NULL != $graph->countValues($fakeIri, 'nfo:uuid')) {
            $existingUuid = $graph->getLiteral($fakeIri, 'nfo:uuid');
            // Delete isl:hasURN to make it match the uuid
            if (NULL != $graph->countValues($fakeIri, 'isl:hasURN')) {
              $graph->delete($fakeIri, 'isl:hasURN');
            }
            $graph->addResource($fakeIri, 'isl:hasURN', 'urn:uuid:'.$existingUuid); //Testing an Islandora Ontology!
          } else {
            //No UUID from the caller in RDF, lets put something there
            // Need a random UUID because there wasn't one provided.
            $newUuid = $this->uuidGenerator->generateV4();
            if (NULL != $graph->countValues($fakeIri, 'isl:hasURN')) {
              $graph->delete($fakeIri, 'isl:hasURN');
            }
            $graph->addLiteral($fakeIri,"nfo:uuid",$newUuid); //Keeps compat for now with other services
            $graph->addResource($fakeIri,"isl:hasURN",'urn:uuid:'.$newUuid); //Testing an Islandora Ontology
          }
          //Restore LDP <> IRI on serialised graph 
          $pcmd_object_rdf= str_replace($fakeIri, '', $graph->serialise('turtle'));
        }

        $urlRoute = $request->getUriForPath('/islandora/resource/');

        $subRequestPost = Request::create($urlRoute.$id, 'POST', array(), $request->cookies->all(), array(), $request->server->all(),  $pcmd_object_rdf);
        $subRequestPost->query->set('tx', $tx);
        $subRequestPost->headers->set('Content-Type', 'text/turtle');
        $responsePost = $app->handle($subRequestPost, HttpKernelInterface::SUB_REQUEST, false);

        if (201 == $responsePost->getStatusCode()) {// OK, object created
          //Lets take the location header in the response
          $direct_container_rdf = $app['twig']->render('createdirectContainerfromTS.ttl', array(
            'resource' => $responsePost->headers->get('location'),
          ));

          $subRequestPut = Request::create($urlRoute.$id, 'PUT', array(), $request->cookies->all(), array(), $request->server->all(),$direct_container_rdf);
          $subRequestPut->query->set('tx', $tx);
          $subRequestPut->headers->set('Slug', 'files');
          //Can't use in middleware, but needed. Without Fedora 4 throws big java errors!
          $subRequestPut->headers->set('Host', $app['config']['islandora']['fedoraHost'], TRUE);
          $subRequestPut->headers->set('Content-Type', 'text/turtle');
          //Here is the thing. We don't know if UUID of the object we just created is already in the tripple store.
          //So what to do? We could just try to use our routes directly, but UUID check agains triplestore we could fail!
          //lets invoke the controller method directly
          $responsePut = $app['islandora.resourcecontroller']->put($app, $subRequestPut, $responsePost->headers->get('location'), "files");
          if (201 == $responsePut->getStatusCode()) {// OK, direct container created
            //Include headers from the parent one, some of the last one. Basically rewrite everything
            $putHeaders = $responsePut->getHeaders();
            //Guzzle psr7 response objects are inmutable. So we have to make this an array and add directly
            $putHeaders['Link'] = array('<'.$responsePut->getBody().'>; rel="alternate"');
            $return_uuid = (isset($existingUuid) ? $existingUuid : $newUuid);
            $putHeaders['Link'] = array('<'.$urlRoute.$return_uuid.'/files>; rel="hub"');
            $putHeaders['Location'] = array($urlRoute.$return_uuid);
            //Should i care about the etag?
            return new Response($putHeaders['Location'][0], 201, $putHeaders);
          }

          return $responsePut;
        }
        //Abort if PCDM object object could not be created
        $app->abort($responsePost->getStatusCode(), 'Failed creating PCDM Object');
    }
    
    /**
     * Add a Binary to the pcdm object for the files object.
     *
     * @param Application $app
     *   The silex application.
     * @param Request $request
     *   The Symfony request.
     * @param string $id
     *   The UUID of the pcdm object.
     * @param string $file
     *   The slug of the file to be added to the pcmd:object
     */
    public function addImage(Application $app, Request $request, $id, $file) {
 
      return $response;

    }
    
  