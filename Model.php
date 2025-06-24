<?php

/**
 * Core Framework - GroupsModel
 *
 * @license    MIT (https://mit-license.org/)
 * @author     Louis Ouellet <louis@laswitchtech.com>
 */

// Import additionnal class into the global namespace
use \LaswitchTech\Core\Abstracts\Model;

class GroupsModel extends Model {

    /**
     * Retrieve the list of Groups
     *
     * @return array
     */
    public function list(): array
    {
        // Retrieve the Groups
        $Query = $this->Database->query()
            ->table('groups')
            ->select('*')
            ->join('owner', 'users', 'username')
            ->where('id', 9999, '<>');

        // Fetch the Groups
        $groups = $Query->fetch();

        // Sanitize the Groups
        foreach($groups as $key => $group){
            $groups[$key]['users'] = json_decode($group['users'] ?? '[]', true);
        }

        // Return the Groups
        return $groups;
    }

    /**
     * Retrieve Groups's Details
     *
     * @param int $id
     * @param bool $all
     * @return array
     */
    public function get(int $id, bool $all = true): array
    {
        // Retrieve the Group
        $Query = $this->Database->query()
            ->table('groups')
            ->select('*')
            ->where('id', $id)
            ->where('id', 9999, '<>')
            ->limit(1);

        // Fetch the Groups
        $groups = $Query->fetch();

        // Loop through the Groups
        foreach($groups as $key => $group){

            // Decode JSON Fields
            $group['users'] = json_decode($group['users'] ?? '[]', true);

            // Check if all the details should be retrieved
            if($all){

                // Retrieve the Users
                $users = [];
                foreach($group['users'] as $key => $user){

                    // Retrieve the User
                    $Query = $this->Database->query()
                        ->table('users')
                        ->select('*')
                        ->join('owner', 'users', 'username')
                        ->join('vcard', 'vcards', 'id')
                        ->where('id', 9999, '<>')
                        ->where('id', $user)
                        ->limit(1);
                    $users[$user] = $Query->fetch()[0] ?? [];
                }
                $group['users'] = $users;

                // Retrieve the Events
                $Query = $this->Database->query()
                    ->table('events')
                    ->select('*')
                    ->where('targetTable', 'groups')
                    ->where('targetId', $group['id'])
                    ->where('id', 9999, '<>')
                    ->index('id');
                $group['events'] = $Query->result();
            }

            // Save the Group
            return $group;
        }

        // Return the Group
        return [];
    }

    /**
     * Create a new group and return the id
     *
     * @param array $data
     * @return int
     */
    public function create(array $data): int
    {
        // Create the Query
        $Query = $this->Database->query()
            ->table('groups')
            ->insert($data);

        // Execute the Query
        $affectedRows = $Query->execute();

        // Execute the Query
        return $Query->lastId();
    }

    /**
     * Update a group
     *
     * @param int $id
     * @param array $data
     * @return int
     */
    public function update(int $id, array $data): int
    {
        // Create the Query
        $Query = $this->Database->query()
            ->table('groups')
            ->update($data)
            ->where('id', $id);

        // Execute the Query
        return $Query->execute();
    }

    /**
     * Retrieve the list of Users
     *
     * @return array
     */
    public function users(): array
    {
        // Retrieve the Groups
        $Query = $this->Database->query()
            ->table('users')
            ->select('*')
            ->join('owner', 'users', 'username')
            ->join('vcard', 'vcards', 'id')
            ->where('id', 9999, '<>')
            ->index('id');

        // Fetch the Groups
        $users = $Query->fetch();

        // Sanitize the Groups
        foreach($users as $key => $user){
            $users[$key]['vcard']['tags'] = json_decode($user['vcard']['tags'] ?? '[]', true);
            $users[$key]['vcard']['industries'] = json_decode($user['vcard']['industries'] ?? '[]', true);
        }

        // Return the Users
        return $users;
    }
}
