<?php
class Services_DoingTextTest extends PHPUnit_Framework_TestCase
{
    
    protected $password;
    protected $permaLink;
    protected $username;
    
    public function setUp()
    {
        if (file_exists(dirname(__FILE__) . '/test-conf.php')) {
            include dirname(__FILE__) . '/test-conf.php';
            $this->username  = $username;
            $this->password  = $password;
            $this->permaLink = $permaLink;
        } else {
            $this->markTestSkipped('Need a test-conf.php to run!');
        }
    }

    public function testIfCreatingADiscussionWorks()
    {
        //$dt = new Services_DoingText($this->username, $this->password);
        
        //$permaLink = 'till' . date('YmdHis');
        
        //$dt->add('Lorem ipsum.', $permaLink, sha1($permaLink));
    }

    public function testIfIncorrectUserThrowsException()
    {
        $this->setExpectedException('Services_DoingText_Exception');

        $dt = new Services_DoingText('till', 'incorrect');
        $dt->get();
    }

    public function testIfWeCanPullADiscussionByPermaLink()
    {
        $dt         = new Services_DoingText($this->username, $this->password);
        $discussion = $dt->get($this->permaLink);

        $this->assertEquals(true, is_array($profile));
        $this->assertEquals($this->permaLink, $discussion['permalink']);
    }

    public function testIfWeSeeProfileWithDiscussions()
    {
        $dt      = new Services_DoingText($this->username, $this->password);
        $profile = $dt->get();

        $this->assertEquals(true, is_array($profile));
    }
}