<?php

namespace Symbiote\GridFieldExtensions;

use SilverStripe\Control\RequestHandler;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\ORM\DataObjectInterface;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Security\Security;
use SilverStripe\Control\Controller;
use SilverStripe\ORM\ValidationResult;
use SilverStripe\Control\HTTPResponse;

class GridFieldRecordActionHandler extends RequestHandler
{

    private static $allowed_actions = [
        'publish',
        'unpublish',
        'archive'
    ];

    private static $url_handlers = [
        '$Action!' => '$Action'
    ];

    protected $record = null;

    public function __construct(GridField $gridField, DataObjectInterface $record)
    {
        $this->gridField = $gridField;
        $this->record = $record;
        parent::__construct();
    }

    public function publish(HTTPRequest $request)
    {
        if ($this->record->canPublish()) {
            $this->record->publishRecursive();
            return $this->finishHandling($request, 'Published');
        }
        return Security::permissionFailure($this);
    }

    public function unpublish(HTTPRequest $request)
    {
        if ($this->record->canUnpublish()) {
            $this->record->doUnpublish();
            return $this->finishHandling($request, 'Unpublished');
        }
        return Security::permissionFailure($this);
    }

    public function archive(HTTPRequest $request)
    {
        if ($this->record->canArchive()) {
            $this->record->doArchive();
            return $this->finishHandling($request, 'Archived');
        }
        return Security::permissionFailure($this);
    }

    /**
     * Return the most appropriate response for our position in the CMS
     * and display the feedback to the user.
     * @param HTTPRequest $request
     * @param string $message User feedback
     * @return HTTPResponse
     */
    protected function finishHandling($request, $messageType)
    {
        $response = HTTPResponse::create();

        // Set the more immeditate 'toast' style notification
        $message = $this->generateMessage($messageType);
        $response->addHeader('X-Status', rawurlencode($message));

        // Display feedback atop the form this action has originated from
        $message = $this->generateMessage($messageType, true);
        $this->gridField->getForm()->sessionMessage($message, 'good', ValidationResult::CAST_HTML);

        $controller = Controller::curr();
        if ($controller->hasMethod('getResponseNegotiator')) {
            $controller->setResponse($response);
            return $controller->getResponseNegotiator()->respond($request);
        }
        return $response;
    }

    /**
     * Helper method to fetch a localised feedback message, defaulting to English
     * @param string $translationID "Published" | "Unpublished" | "Archived"
     * @return string Message in local translation if it exsits, or in English.
     */
    protected function generateMessage($translationID, $useHTML = false)
    {
        $title = htmlspecialchars($this->record->Title, ENT_QUOTES);
        if ($useHTML) {
            $link = Controller::join_links($this->gridField->Link('item'), $this->record->ID, 'edit');
            $title = '<a href="' . $link . '">"' . $title . '"</a>';
        }
        $message = _t(
            'Symbiote\\GridFieldExtensions\\GridFieldRecordActionHandler.' . $translationID,
            "$translationID {type} {title}",
            array(
                'type' => $this->record->i18n_singular_name(),
                'title' => $title
            )
        );
        return $message;
    }
}
