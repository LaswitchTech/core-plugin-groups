<?php

/**
 * Core Framework - GroupsEndpoint
 *
 * @license    MIT (https://mit-license.org/)
 * @author     Louis Ouellet <louis@laswitchtech.com>
 */

// Import additionnal class into the global namespace
use \LaswitchTech\Core\Objects;
use \LaswitchTech\Core\Abstracts\Endpoint;

class GroupsEndpoint extends Endpoint {

    /**
     * Constructor
     */
    public function __construct()
    {

        // Call Parent Constructor
        parent::__construct();

        // Retrieve the namespace
        $namespace = $this->Request->getNamespace();

        // Set Global access
        $this->Public = false;

        // Set Level
        switch($namespace){
            case "/groups/index":
            case "/groups/fetch":
            case "/groups/users":
                $this->Level = 1;
                break;
            case "/groups/update":
                $this->Level = 3;
                break;
        }
    }

    /**
     * Fetch all groups
     */
    public function indexAction(): array
    {
        // Set the default message
        $message = ["status" => 200, "message" => "OK", "data" => $this->Model->Groups->list()];

        // Return the message
        return $message;
    }

    /**
     * Fetch a Group's Information
     */
    public function fetchAction(): array
    {
        // Import Global Variables
        global $CONFIG;

        // Set the default message
        $message = ["status" => 200, "message" => "OK", "data" => [
            "record" => $this->Model->Groups->get(intval($this->Request->getParams('GET', 'id')))
        ]];

        // Return the message
        return $message;
    }

    /**
     * Update a Group
     */
    public function updateAction(): array
    {
        // Import Global Variables
        global $CSRF;

        // Set the default message
        $message = ["status" => 200, "message" => "OK", "data" => []];

        // Check the request method
        if($this->Request->getMethod() == "POST"){
            $message["data"]["CSRF"] = [
                "token" => $CSRF->token(),
                "key" => $CSRF->key()
            ];
        }

        // Retrieve the group id
        $id = intval($this->Request->getParams('REQUEST','id'));

        // Retrieve the group
        $group = $this->Model->Groups->get($id, false);

        // Check if the group exists
        if(empty($group)){
            $message = ["status" => 404, "message" => "Not Found", "data" => "Could not find the requested group."];
        }

        // Check if the group is accessible
        if($message['status'] == 200){

            // Check the request method
            if($this->Request->getMethod() == "POST"){

                // Retrieve the parameters
                $parameters = $this->Request->getParams('REQUEST');

                // Initialize the Events
                $message['data']['events'] = [];

                // Update the group
                foreach($parameters as $key => $value){
                    if(isset($group[$key])){
                        switch($key){
                            case 'users':
                                if(is_array($value)){
                                    $group[$key] = [];
                                    foreach($value as $objId){
                                        $group[$key][] = intval($objId);
                                    }
                                    $group[$key] = array_unique($group[$key]);
                                } else {
                                    $group[$key] = $value;
                                }
                                break;
                            case 'isDefault':
                                $group[$key] = intval(filter_var($value, FILTER_VALIDATE_BOOLEAN));
                                break;
                            default:
                                $group[$key] = $value;
                                break;
                        }
                    }
                }

                // Update the group
                $affectedRows = $this->Model->Groups->update($id, $group);

                // Retrieve the final group
                $message['data']['record'] = $this->Model->Groups->get($id);
            } else {
                $message = ["status" => 405, "message" => "Method Not Allowed", "data" => "The method is not allowed for the requested URL."];
            }
        }

        return $message;
    }

    /**
     * Fetch all users
     */
    public function usersAction(): array
    {
        // Set the default message
        $message = ["status" => 200, "message" => "OK", "data" => $this->Model->Groups->users()];

        // Return the message
        return $message;
    }
}
