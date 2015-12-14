<?php
namespace neamtua;

class SecretSanta
{
    private $database = [
        'server' => '',
        'username' => '',
        'password' => '',
        'database' => ''
    ];

    private function connectToDatabase()
    {
        # try connecting to database
        $connection = new \mysqli(
            $this->database['server'],
            $this->database['username'],
            $this->database['password'],
            $this->database['database']
        );

        # connection failed
        if ($connection->connect_errno) {
            throw new \Exception('Could not connect to database.');
        }

        return $connection;
    }

    public function getListOfPeople()
    {
        # connect to database
        $connection = $this->connectToDatabase();

        # run query
        $sql = 'SELECT id, name FROM santas ORDER BY name ASC';
        $result = $connection->query($sql);
        if (!$result) {
            throw new \Exception('Could not retrieve data.');
        }

        # close database connection
        $connection->close();

        # build array value
        $values = $result->fetch_all();

        # cleanup
        $result->free();

        # return values
        return $values;
    }

    public function showGiftee($santaId)
    {
        # connect to database
        $connection = $this->connectToDatabase();

        # run query
        $sql = 'SELECT has_extracted, giftee FROM santas WHERE id=? LIMIT 1';
        $query = $connection->prepare($sql);
        $query->bind_param('i', $santaId);
        $query->execute();
        $query->bind_result($extracted, $giftee);
        $query->fetch();
        $query->close();

        # return value
        if (empty($extracted)) {
            # update
            $sql = 'UPDATE santas SET has_extracted = 1 WHERE id = ? LIMIT 1';
            $query = $connection->prepare($sql);
            $query->bind_param('i', $santaId);
            $query->execute();

            # close database connection
            $connection->close();

            # return giftee
            return $giftee;
        }

        # close database connection
        $connection->close();

        # return nothing
        return '';
    }

    private function checkIfPairsHaveBeenGenerated()
    {
        # connect to database
        $connection = $this->connectToDatabase();

        # run query
        $sql = 'SELECT id FROM santas WHERE giftee IS NULL LIMIT 1';
        $result = $connection->query($sql);
        if (!$result) {
            throw new \Exception('Could not retrieve data.');
        }

        return ($result->num_rows)?false:true;
    }

    public function generatePairs()
    {
        # if pairs have been generated, do not generate
        if ($this->checkIfPairsHaveBeenGenerated()) {
            return;
        }

        $santas = [];
        $pairs = [];

        $people = $this->getListOfPeople();
        foreach ($people as $row) {
            $santas[$row[0]] = $row[1];
        }

        $givers = $santas;
        $receivers = $santas;

        foreach ($givers as $id => $giver) {
            $hasReceived = false;
            while (!$hasReceived) {
                # pick random person
                $receiver = rand(0, count($receivers)-1);
                if ($receivers[$receiver] != $giver) {
                    # hurray, you have a match
                    $pairs[] = [
                        'id' => $id,
                        'santa' => $giver,
                        'giftee' => $receivers[$receiver]
                    ];

                    # we remove him from the list
                    unset($receivers[$receiver]);
                    $receivers = array_values($receivers);

                    # mkay, we're done here
                    $hasReceived = true;
                } else {
                    # particular case where there is only 1 user left and has received himself
                    if (count($receivers) == 1) {
                        # we have to swap with the first guy
                        $pairs[] = [
                            'id' => $id,
                            'santa' => $giver,
                            'giftee' => $pairs[0]['giftee']
                        ];
                        $pairs[0]['giftee'] = $giver;

                        # mkay, we're done here
                        $hasReceived = true;
                    }
                }
            }
        }

        # connect to database
        $connection = $this->connectToDatabase();

        # update pairs in database
        $sql = 'UPDATE santas SET giftee = ?, has_extracted = 0 WHERE id = ? LIMIT 1';
        foreach ($pairs as $pair) {
            $query = $connection->prepare($sql);
            $query->bind_param('si', $pair['giftee'], $pair['id']);
            $query->execute();
        }

        # close database connection
        $connection->close();
    }

    public function resetSantas()
    {
        # connect to database
        $connection = $this->connectToDatabase();

        # update pairs in database
        $sql = 'UPDATE santas SET has_extracted = 0, giftee = NULL';
        $connection->query($sql);

        # close database connection
        $connection->close();
    }

}
