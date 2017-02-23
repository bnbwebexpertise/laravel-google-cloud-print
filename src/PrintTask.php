<?php
namespace Bnb\GoogleCloudPrint;

use Bnb\GoogleCloudPrint\Exceptions\InvalidSourceException;
use Bnb\GoogleCloudPrint\Exceptions\PrintTaskFailedException;

class PrintTask
{

    protected $accessToken;

    protected $printer;

    protected $contentType;

    protected $title;

    protected $tags = [];

    protected $source = 'No content';

    protected $printOptions = [];

    private $tryProcessInvite = true;


    /**
     * PrintTask constructor.
     *
     * @param string $accessToken Google OAuth2 access token
     * @param string $contentType MIME content type
     */
    public function __construct($accessToken, $contentType)
    {
        $this->accessToken = $accessToken;
        $this->contentType = $contentType;
    }


    /**
     * @param string $raw The raw content to print
     *
     * @return self
     */
    public function content($raw)
    {
        $this->source = $raw;

        return $this;
    }


    /**
     * @param string $file An accessible file path
     *
     * @return PrintTask
     * @throws InvalidSourceException
     */
    public function file($file)
    {
        if ( ! file_exists($file)) {
            throw new InvalidSourceException();
        }

        $this->source = file_get_contents($file);

        return $this;
    }


    /**
     * @param string $url An absolute public URL (prefixed by http or https)
     *
     * @return self
     * @throws InvalidSourceException
     */
    public function url($url)
    {
        if ( ! preg_match('/^https?:\/\//', $url)) {
            throw new InvalidSourceException();
        }

        $this->source = file_get_contents($url);

        return $this;
    }


    /**
     * @param string $title The task title
     *
     * @return self
     */
    public function title($title)
    {
        $this->title = $title;

        return $this;
    }


    /**
     * @param string $printer The printer ID
     *
     * @return self
     */
    public function printer($printer)
    {
        $this->printer = $printer;

        return $this;
    }


    /**
     * @param array|string|string... $tags
     *
     * @return self
     */
    public function tags($tags)
    {
        if (is_array($tags)) {
            $this->tags = $tags;
        } elseif (func_num_args() > 1) {
            $this->tags = func_get_args();
        } else {
            $this->tags[] = $tags;
        }

        $this->tags = array_map('str_slug', $this->tags);

        return $this;
    }


    /**
     * @param int $start
     * @param int $end
     *
     * @return self
     */
    public function range($start, $end)
    {
        if ($start > $end) {
            $tmp = $start;
            $start = $end;
            $end = $tmp;
        }

        $this->ticket('page_range', [
            'interval' => [
                [
                    'start' => $start,
                    'end' => $end
                ]
            ]
        ]);

        return $this;
    }


    /**
     * Sets the margins in millimeters
     *
     * @param int $top
     * @param int $right
     * @param int $bottom
     * @param int $left
     *
     * @return PrintTask
     */
    public function marginsInMillimeters($top, $right, $bottom, $left)
    {
        $this->ticket('margins', [
            'top_microns' => $top * 1000,
            'right_microns' => $right * 1000,
            'bottom_microns' => $bottom * 1000,
            'left_microns' => $left * 1000,
        ]);

        return $this;
    }


    /**
     * Sets the margins in centimeters
     *
     * @param int $top
     * @param int $right
     * @param int $bottom
     * @param int $left
     *
     * @return PrintTask
     */
    public function marginsInCentimeters($top, $right, $bottom, $left)
    {
        return $this->marginsInMillimeters($top * 10, $right * 10, $bottom * 10, $left * 10);
    }


    /**
     * @param string $key
     * @param mixed  $value
     *
     * @return self
     */
    public function ticket($key, $value)
    {
        $this->printOptions[$key] = $value;

        return $this;
    }


    /**
     * @return PrintJob
     *
     * @throws PrintTaskFailedException
     */
    public function send()
    {
        $ticket = [
            'version' => '1.0'
        ];

        if ( ! empty($this->printOptions)) {
            $ticket['print'] = $this->printOptions;
        }

        $job = PrintApi::submit($this->accessToken, $this->printer, [
            'title' => $this->title,
            'contentTransferEncoding' => 'base64',
            'content' => base64_encode($this->source),
            'contentType' => $this->contentType,
            'tag' => join(',', $this->tags),
            'ticket' => json_encode($ticket)
        ]);

        if ($job && ($job = json_decode($job))) {

            if ($job->success) {
                return new PrintJob($job->job);
            }

            if ($job->errorCode === 8 && $this->tryProcessInvite) {
                $this->tryProcessInvite = false;

                $invite = PrintApi::processInvite($this->accessToken, $this->printer);

                if ($invite) {
                    $invite = json_decode($invite);

                    if ($invite->success) {
                        return $this->send();
                    }
                }
            }
        }

        throw new PrintTaskFailedException(sprintf('The print job submission has failed : %s', json_encode($job ?: 'Unknown error')));
    }
}
