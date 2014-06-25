zhort
=====

Class to short urls

demo: (http://zhort.tk)

Usage:
```php
$zhort = new Zhort();

$href = $zhort->getUrlFromDB($_GET['url']);

if($_GET['url'] != '' && $url) {
    header("Location: ". $url);
}
elseif(!empty($_POST['url'])) {
    try {
            $zhort->setUrl($_POST['url']);
            $zhort->setName($_POST['name']);
            $zhort->validUrl();
            echo 'http://' . $_SERVER['SERVER_NAME'] . '/' . $zhort->addUrl();
            
        } catch (Exception $e) {
        echo $e->getMessage();
      }
