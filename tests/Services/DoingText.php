<?php
class Services_DoingTextTest extends PHPUnit_Framework_TestCase
{
    protected $username;
    protected $password;
    
    public function setUp()
    {
        if (file_exists(dirname(__FILE__) . '/test-conf.php')) {
            include dirname(__FILE__) . '/test-conf.php';
            $this->username = $username;
            $this->password = $password;
        } else {
            $this->markTestSkipped('Need a test-conf.php to run!');
        }
    }
    
    public function testIfWeSeeProfileWith()
    {
        $dt = new Services_DoingText($this->username, $this->password);
        $dt->get();
    }
    
    public function testIfIncorrectUserThrowsException()
    {
        $dt = new Services_DoingText('till', 'incorrect');
        $dt->get();
        
        $this->setExpectedException('Services_DoingText_Exception');
    }
}