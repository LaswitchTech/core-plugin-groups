<?php

require_once __DIR__ . '/../Model.php';

class GroupsPostModel extends GroupsModel {

    /**
     * Post process a record
     *
     * @param array $record
     * @return array
     */
    public function post($record): array
    {
        // Check if the record ID is below 9999
        if($record['id'] > 9999) return $record;

        // Loop through the record
        foreach($record as $key => $value){

            // Handle specific fields
            switch($key){
                case 'id':
                case 'users':
                    break;
                default:
                    unset($record[$key]);
                    break;
            }
        }

        // Return the record
        return $record;
    }
}
