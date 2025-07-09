<?php

// Import additionnal class into the global namespace
use \LaswitchTech\Core\Base\BaseModel;

class GroupsModel extends BaseModel {

    /**
     * Constructor
     */
    public function __construct()
    {
        // Call the parent constructor
        parent::__construct();

        // Initialize the Model
        $this->init('groups');
    }

    /**
     * Update a record
     *
     * @param int $id
     * @param array $data
     * @return int
     */
    public function update(int $id, array $data): int
    {
        // Sanitize the Data
        foreach($data as $key => $value){

            // Add exceptions for specific fields
            if($key === 'users' && is_array($value)){

                // Loop through each userId in the array
                foreach($value as $userKey => $userId){

                    // Convert the userId to an integer
                    $value[$userKey] = (int)$userId;
                }

                // Filter unique user IDs
                $value = array_unique($value);
            }

            // Set the value back to the data array
            $data[$key] = $value;
        }

        // Call the parent update method
        return parent::update($id, $data);
    }
}
