# Google Cloud Print service for Laravel 5

## Install

Via composer :

    composer require bnbwebexpertise/laravel-google-cloud-print

Then add the service provider class to your Laravel `config/app.php` :

    BnB\GoogleCloudPrint\LaravelServiceProvider::class

## Configuration

Set the env parameter `GCP_CREDENTIALS_PATH` to the absolute path
 (or relative to the laravel application root) of the servie account
 JSON file downloaded from Google Console.

### Google service setup

Create a service account key (IAM) with a `***@re-speedy-diagnostic.iam.gserviceaccount.com`
 and download the JSON key file at [https://console.developers.google.com/apis/credentials](https://console.developers.google.com/apis/credentials).
 Copy the file into the project at the configured env path.

You also need to allow print access to the generated email address on
 all the desired printers via the Google Cloud Print console at
 [https://www.google.com/cloudprint/#printers](https://www.google.com/cloudprint/#printers).

This library will attempt to accept the invite if the Google API rejects
 the credentials. Indeed Google service accounts do not get the invitation
 email with the accept link and therefore need to use the API to complete
 the process.

## Usage

### Create a print task

Either use the Facade or the shortcut with one of the three provided
 content type to get a print task object :

```
$task = GoogleCloudPrint::asText()
$task = GoogleCloudPrint::asHtml()
$task = GoogleCloudPrint::asPdf()

// or

$task = app('google.print')->asText()
$task = app('google.print')->asHtml()
$task = app('google.print')->asPdf()

```

#### Configure and send the print task

Calling `->printer($printerId)` is required. The `$printerId` is the
 printer's UUID you get on the printer details page at Google Cloud Print
 console (or in the printer URL).

The content can be provided in three way :
 - raw via `->content('A raw content')`.
 - local file via `->file('/path/to/my/file')`. An exception is thrown if the file is not accessible
 - url via `->url('http://acme.foo/bar')`. The content is downloaded locally before sending the print job. An exception is thrown if the URL does not begin with `http(s)://`

You can set any other Cloud Job Ticket option via the `->ticket($key, $value)` method.
 Some helpers are provided :
 - range helper via `->range($start, $end)` (start and end pages are included).
 - margins helpers via the `->marginsInMillimeters($top, $right, $bottom, $left)` and `->marginsInCentimeters($top, $right, $bottom, $left)`.


If the job is rejected an exception is thrown.

#### Examples

```
$printerId = 'xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx';

// Printing HTML from an URL
GoogleCloudPrint::asHtml()
    ->url('https://opensource.org/licenses/MIT')
    ->printer($printerId)
    ->send();

// Printing page 3 to 10 of a PDF from a local file
GoogleCloudPrint::asPdf()
    ->file('storage/document.pdf')
    ->range(3, 10)
    ->printer($printerId)
    ->send();

// Printing plain text with a 1cm margin on each sides using
GoogleCloudPrint::asText()
    ->content('This is a test')
    ->printer($printerId)
    ->marginsInCentimeters(1, 1, 1, 1)
    ->send();
```