<?php

namespace Tarosky\TaroMultisiteSynchronizer\Pattern;

use Tarosky\TaroMultisiteSynchronizer\Models\BlogComments;
use Tarosky\TaroMultisiteSynchronizer\Models\Blogs;


/**
 * Model accessor
 *
 * @package TaroMultiSite\Pattern
 * @property-read Blogs $blogs
 * @property-read BlogComments $comments
 */
final class ModelAccessor extends Singleton
{
	/**
	 * Getter
	 *
	 * @param string $name
	 * @return mixed
	 */
	public function __get($name){
		switch( $name ){
			case 'blogs':
				return Blogs::get_instance();
				break;
			case 'comments':
				return BlogComments::get_instance();
			default:
				return null;
				break;
		}
	}

}
