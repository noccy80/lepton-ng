<?php

/**
 * Lepton Smarty-based Blog Controller
 *
 * Renders your blogs in full color with the use of Smarty templates!
 */
 
ModuleManager::load('lepton.cms.*');
ModuleManager::load('lepton.mvc.viewhandler.smarty');

class BlogController extends Controller {

	/**
	 *
	 */
	function __request($method=null,$args=null) {
		switch($method) {
			case 'tag':
				call_user_func_array(array($this,'showtag'),$args);
				break;
			case 'category':
				call_user_func_array(array($this,'category'),$args);
				break;
			case 'video':
				call_user_func_array(array($this,'video'),$args);
				break;			
			case 'image':
				call_user_func_array(array($this,'image'),$args);
				break;
			case 'post':
				call_user_func_array(array($this,'posts'),$args);
				break;
			case 'comments':
				call_user_func_array(array($this,'comments'),$args);
				break;
			default:
				call_user_func_array(array($this,'index'),$args);
		}
	}
	
	function index() {
		$posts = array(
			array(
				'id' => 125923,
				'slug' => 'the-most-awesome-post-ever',
				'title' => 'The Most Awesome Post! Ever!',
				'posted' => '2010-09-01 02:0:00',
				'text' => '<p>This is the most awesome post. ever</p><p>And the second line of it.</p>'
			),
			array(
				'id' => 125922,
				'slug' => 'the-second-most-awesome-post-ever',
				'title' => 'The Second Most Awesome Post! Ever!',
				'posted' => '2010-09-01 01:00:00',
				'text' => '<p>This is the second most awesome post. ever</p><p>And the second line of it.</p>'
			)
		);
		$this->blogposts = $posts;
		View::load('blog/default.php',$this);
	}

}
