<div class="wrap">

	<?php $form = new PW_MultiModelForm( $model ); ?>	

	<?php $form->begin_form(); ?>
	
		<?php $form->begin_section('Settings'); ?>
		<ul>
			<li><?php $form->textfield( 'name', array('class'=>'large-text code') ); ?></li>
			<li><?php $form->checkbox_list( 'post_types', '&nbsp;&nbsp;' ); ?></li>
			<li><?php $form->radio_button_list( 'depth' ); ?></li>
			<li><?php $form->select( 'order' ); ?></li>
			<li><?php $form->select( 'year_count'); ?></li>
			<li><?php $form->radio_button_list( 'layout' ); ?></li>	
		</ul>
		<?php $form->end_section(); ?>

		
		<?php $form->begin_section('Javascript'); ?>
		<ul>
			<li><?php $form->checkbox_list( 'js' ); ?></li>
			<li><?php $form->select( 'js_event' ); ?></li>
			<li><?php $form->select( 'js_effect' ); ?></li>
			<li><?php $form->checkbox( 'css', array('value'=>'YES'), 'NO'); ?></li>		
		</ul>
		<?php $form->end_section(); ?>


		<?php $form->begin_section('Year Format'); ?>
		<ul>
			<li><?php $form->textfield( 'year_format', array('class'=>'small-text code') ); ?></li>
			<li><?php $form->textfield( 'year_template', array('class'=>'large-text code') ); ?></li>
		</ul>
		<?php $form->end_section(); ?>


		<?php $form->begin_section('Month Format'); ?>
		<ul>
			<li><?php $form->textfield( 'month_format', array('class'=>'small-text code') ); ?></li>
			<li><?php $form->textfield( 'month_template', array('class'=>'large-text code') ); ?></li>
		</ul>
		<?php $form->end_section(); ?>


		<?php $form->begin_section('Post Format'); ?>
		<ul>
			<li><?php $form->textfield( 'post_date_format', array('class'=>'small-text code') ); ?></li>
			<li><?php $form->textfield( 'post_template', array('class'=>'large-text code') ); ?></li>
		</ul>
		<?php $form->end_section(); ?>
		
	<?php $form->end_form(); ?>
	
	<div class="pw-form-footer">
		<h3>Suggestions?</h3>
		<p>Have an idea about how to improve PW_Archives? Send me an <a href="mailto:philip@philipwalton.com">email</a> or leave a comment on the <a href="http://philipwalton.com/2011/02/08/pw_archives/">PW_Archives blog post.</a></p>
	</div>

</div>


	
	

