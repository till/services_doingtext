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
        $dt = new Services_DoingText($this->username, $this->password);
        
        $permaLink = 'tfk' . substr(sha1('till' . date('YmdHis')), 0, 8);
        
        $title = 'Test POST Services_DoingText';
        
        $text  = 'Lorem ipsum.' . date('YmdHis');
        $text .= "\n" . 'Titel: ' . $title;
        $text .= "\n" . 'Permalink: ' . $permaLink;
        
        $discussion = $dt->add(
            $text,
            $title,
            $permaLink
        );
        $this->assertEquals($permaLink, $discussion['permalink']);
        
        // try again to make sure :-)
        $discussion2 = $dt->get($permaLink);
        $this->assertEquals($permaLink, $discussion2['permalink']);
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

        $this->assertEquals(true, ($discussion instanceof Services_DoingText_Response));
        $this->assertEquals($this->permaLink, $discussion['permalink']);
    }

    public function testIfWeSeeProfileWithDiscussions()
    {
        $dt      = new Services_DoingText($this->username, $this->password);
        $profile = $dt->get();

        $this->assertEquals(true, ($profile instanceof Services_DoingText_Response));
    }
}