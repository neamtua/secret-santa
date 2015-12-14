# secret-santa
A PHP/MySQL Secret Santa pairing class

Pieced this together to make a small website for me and my friends to draw for our anually Secret Santa event.

## Usage

### 1. Setting up the database
Enter your database credentials in the class file and import the sql structure from **secretsanta.sql**
```php
private $database = [
    'server' => '',
    'username' => '',
    'password' => '',
    'database' => ''
];
```

### 2. Include the class file
```php
require_once 'SecretSanta.php';
```

### 3. Initialize the class
```php
$secretSanta = new \neamtua\SecretSanta();
```

### 4. Start doing stuff
Generating pairs. The function will check if pairs have already been generated so it doesn't keep overriding them.
```php
$secretSanta->generatePairs();
```
Get the list of people from the database. Use this to generate a dropdown for people to choose their name from and get their pairing.
```php
$santas = $secretSanta->getListOfPeople();
```
Get the pairing of a user. Will only return the pairing the first time it's called for a specific user so that people will not be tempted to take a look at other people's santas.
```php
$giftee = $secretSanta->showGiftee($santaId);
```
Reset the pairings. Use this for next year's draw.
```php
$secretSanta->resetSantas();
```

### 5. Try not to peek at the pairings if you can :)
