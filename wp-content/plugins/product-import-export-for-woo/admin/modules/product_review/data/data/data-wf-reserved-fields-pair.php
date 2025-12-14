<?php

// Reserved column names
return  apply_filters('woocommerce_csv_product_review_import_reserved_fields_pair', array(
                
                'comment_ID'                    => array('title'=>'Comments ID','description'=>'ID of the comments'),
		'product_SKU'		   	=> array('title'=>'Product SKU','description'=>'Product SKU'),
                'comment_post_ID'               => array('title'=>'Comment Post ID','description'=>'ID of the product, on which the comment is done'),
                'comment_author'                => array('title'=>'Comments Author Name','description'=>'The author name, who made comments'),
                'comment_author_url'            => array('title'=>'Comments Author URL','description'=>'The author URL, who made comments'),
                'comment_author_email'          => array('title'=>'Comments Author Email','description'=>'The author email, who made comments'),
                'comment_author_IP'             => array('title'=>'Comments Author IP','description'=>'The author IP, who made comments'),
                'comment_date'                  => array('title'=>'Comments Date','description'=>'The date, when comments is done', 'type' => 'date'),
                'comment_date_gmt'              => array('title'=>'Comments Date(GMT)','description'=>'The date, when comments is done', 'type' => 'date'),
                'comment_content'               => array('title'=>'Comments Content','description'=>'The content of the comments'),
                //'comment_karma'               => array('title'=>'comment_karma',
                'comment_approved'              => array('title'=>'Comments Approved or Not?','description'=>'1, for YES and 0, for NO'),
                'comment_parent'                => array('title'=>'Comments Parent','description'=>'The parent comments id'),
                'user_id'                       => array('title'=>'User ID','description'=>'The user id who comments, if the user is GUEST USER then it is 0'),

                //Meta

                'rating'                        => array('title'=>'Rating','description'=>'1: for 1 star, 2: for 2 star,...'),
                'verified'                      => array('title'=>'Verified or Not?','description'=>'1: for verified, 0: for non-verified'),
		'title'                         => array('title'=>'Review title','description'=>' Review title'),
                'comment_alter_id'              => array('title'=>'Comment Alteration ID','description'=>'System generated'),
    
));