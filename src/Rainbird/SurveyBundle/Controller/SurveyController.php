<?php

namespace Rainbird\SurveyBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;

/**
 * Class SurveyController
 *
 * Controller class for the survey
 *
 * @package Rainbird\SurveyBundle\Controller
 */
class SurveyController extends Controller
{
    /**
     * Renders the initial content of the site
     *
     * @return Response
     */
    public function indexAction()
    {
        try {
            /** @var \GuzzleHttp\Client $client */
            $clientStart = $this->get('guzzle.client.api_start');
            // Get the conversation token for our query
            $responseStart = $clientStart->get($this->getParameter('start_id'));
            // Decode the result
            $responseStartArray = json_decode((string) $responseStart->getBody(), true);
            // If the decoding is successful and the code was returned, add the id to the session, so it can be
            // used for further calls
            if ($responseStartArray['id']) {
                $session = $this->get('session');
                $session->set('conversation_token', $responseStartArray['id']);
            }

            return $this->render('SurveyBundle:Survey:index.html.twig');
        } catch (\Exception $e) {
            return $this->render('SurveyBundle:Survey:error.html.twig', array('code' => $e->getCode()));
        }
    }

    /**
     * Returns the question html to be rendered
     *
     * @param $data
     *
     * @return Response
     */
    public function questionAction($data)
    {
        return $this->render('SurveyBundle:Survey:question.html.twig', array('data' => $data));
    }

    /**
     * Returns the result html to be rendered
     *
     * @param $data
     *
     * @return Response
     */
    public function resultAction($data)
    {
        return $this->render('SurveyBundle:Survey:result.html.twig', array('data' => $data));
    }

    /**
     * Renders the email form html to be rendered
     *
     * @return Response
     */
    public function emailAction()
    {
        return $this->render('SurveyBundle:Survey:email.html.twig');
    }

    /**
     * Renders the error html to be rendered
     *
     * @return Response
     */
    public function errorAction()
    {
        return $this->render('SurveyBundle:Survey:error.html.twig');
    }

    /**
     * Action which processes requests and is used in the AJAX calls
     *
     * @param Request $request
     *
     * @return Response
     */
    public function processAction(Request $request)
    {
        try {
            $session = $this->get('session');
            $conversationToken = $session->get('conversation_token');
            $requestBody = $request->request;
            $email = $requestBody->get('email');
            // If the request contains the email parameter, then the response page is rendered
            // Otherwise a new request is being created
            if ($email) {
                $template = $this->prepareResponse($email);

                return new Response($template);
            } else {
                $requestBodyService = $this->get('request_body_formation');
                $requestBodyService->setRequest($requestBody);
                $body = $requestBodyService->formAPIRequestBody();
                $type = $body['type'];
                $client = $this->get('guzzle.client.api');
                $response = $client->post("/$conversationToken/$type", array('body' => $body['body']));
            }

            // Decode the body of the response
            $responseArray = json_decode((string) $response->getBody(), true);

            // Based on the response type, the rendered content is being selected
            if (isset($responseArray['question']) && $responseArray['question']) {
                $template = $this->forward('SurveyBundle:Survey:question', array('data' => $responseArray['question']))->getContent();
            } else {
                if (isset($responseArray['result']) && $responseArray['result']) {
                    $session->set('result', $responseArray['result']);
                }
                $template = $this->forward('SurveyBundle:Survey:email')->getContent();
            }
        } catch (\Exception $e) {
            $template = $this->forward('SurveyBundle:Survey:error', array('code' => $e->getCode()))->getContent();
        }

        return new Response($template);
    }

    /**
     * Prepares the response content and sends the email
     *
     * @param $email
     *
     * @return string
     */
    protected function prepareResponse($email)
    {
        try {
            $session = $this->get('session');
            $result = $session->get('result');
            // Prepare message
            $message = \Swift_Message::newInstance()
                ->setSubject($this->getParameter('email_subject'))
                ->setFrom($this->getParameter('email_from'))
                ->setTo($email)
                ->addBcc($this->getParameter('email_bcc'))
                ->setBody(
                    $this->renderView(
                        'SurveyBundle:Survey:result.html.twig',
                        array('data' => $result)
                    ),
                    'text/html'
                );
            // Send email
            $this->get('mailer')->send($message);
            // Content of the response which will be rendered
            $template = $this->forward('SurveyBundle:Survey:result', array('data' => $result))->getContent();
        } catch (\Exception $e) {
            $template = $this->forward('SurveyBundle:Survey:error', array('code' => $e->getCode()))->getContent();
        }

        return $template;
    }
}
