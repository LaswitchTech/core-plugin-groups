<?php

class GroupsPostModel extends GroupsModel {

    /**
     * Post process a record
     *
     * @param array $record
     * @return array
     */
    public function post($record): array
    {
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
