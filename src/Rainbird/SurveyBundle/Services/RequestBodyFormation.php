<?php

namespace Rainbird\SurveyBundle\Services;

/**
 * Class RequestBodyFormation
 *
 * Created for manipulating and creating request bodies
 *
 * @package Rainbird\SurveyBundle\Services
 */
class RequestBodyFormation
{
    /**
     * @var \Symfony\Component\HttpFoundation\ParameterBag
     */
    protected $request;

    /**
     * @param \Symfony\Component\HttpFoundation\ParameterBag $request
     */
    public function setRequest($request)
    {
        $this->request = $request;
    }

    /**
     * @return \Symfony\Component\HttpFoundation\ParameterBag
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * Forms the request body for the API
     *
     * @return array
     */
    public function formAPIRequestBody()
    {
        $name = $this->getRequest()->get('name');
        $request = array();
        // If the request contains the 'name' parameter then it is for a query request otherwise it is for a
        // response request
        if ($name) {
            $body = array(
                'subject' => $name,
                'relationship' => 'gets'
            );
            $request['type'] = 'query';
        } else {
            $body = array(
                'answers' => array(
                    array(
                        'subject' => $this->getRequest()->get('subject'),
                        'relationship' => $this->getRequest()->get('relationship'),
                        'object' => $this->getRequest()->get('object'),
                        'cf' => 100,
                    )
                )
            );
            $request['type'] = 'response';
        }
        // Encode the body content as the API client requires
        $request['body'] = json_encode($body);

        return $request;
    }
}