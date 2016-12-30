<?php
/**
 * laravel-google-cloud-print
 *
 * @author    Jérémy GAULIN <jeremy@bnb.re>
 * @copyright 2016 - B&B Web Expertise
 */

namespace BnB\GoogleCloudPrint;

class PrintApi
{

    const URL_SEARCH = 'https://www.google.com/cloudprint/search';
    const URL_SUBMIT = 'https://www.google.com/cloudprint/submit';
    const URL_DELETE_JOB = 'https://www.google.com/cloudprint/deletejob';
    const URL_JOBS = 'https://www.google.com/cloudprint/jobs';
    const URL_PRINTER = 'https://www.google.com/cloudprint/printer';
    const URL_PROCESS_INVITE = 'https://www.google.com/cloudprint/processinvite';

    protected $accessToken;


    private function __construct()
    {

    }


    /**
     * @param string $accessToken OAuth2 offline access token
     * @param string $printer     Printer ID
     * @param array  $options     Print request post fields
     * @param array  $headers     Print request headers
     *
     * @return array
     */
    public static function processInvite($accessToken, $printer)
    {
        $api = new self;
        $api->accessToken = $accessToken;

        $options = [
            'printerid' => $printer,
            'accept' => 'true',
        ];

        return $api->makeHttpCall(self::URL_PROCESS_INVITE, $options);
    }


    /**
     * @param string $accessToken OAuth2 offline access token
     * @param string $printer     Printer ID
     * @param array  $options     Print request post fields
     * @param array  $headers     Print request headers
     *
     * @return array
     */
    public static function submit($accessToken, $printer, $options, $headers = [])
    {
        $api = new self;
        $api->accessToken = $accessToken;

        $options['printerid'] = $printer;

        if (empty($options['title'])) {
            $options['title'] = 'job-' . date('YmdHis') . '-' . rand(1000, 9999);
        }

        return $api->makeHttpCall(self::URL_SUBMIT, $options, $headers);
    }


    /**
     * Makes http calls to Google Cloud Print using curl
     *
     * @param string $url        Http url to hit
     * @param array  $postFields Array of post fields to be posted
     * @param array  $headers    Array of http headers
     *
     * @return mixed
     */
    private function makeHttpCall($url, $postFields = [], $headers = [])
    {
        $headers = array_merge($headers, [
            "Authorization: Bearer " . $this->accessToken
        ]);

        $curl = curl_init($url);

        if ( ! empty($postFields)) {
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $postFields);
        }

        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

        $response = curl_exec($curl);

        curl_close($curl);

        return $response;
    }
}