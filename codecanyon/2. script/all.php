<?php
	require_once("lib/system_load.php");
	//Including this file we load system.
	
	//This loads system.
	authenticate_user('all');
	
	$page_title = _("Accessible only loged in users All types");
	$sub_title = _("Page accessable for all loged in users of anytype.");
	require_once('lib/includes/header.php');
?>
	<div class="row flex-row">
		<div class="col-xl-12">
			<!-- Basic Buttons -->
			<div class="widget has-shadow">
				<div class="widget-header bordered no-actions d-flex align-items-center">
					<h4><?php _e("All users page"); ?></h4>
				</div>
				<div class="widget-body">
					<p><?php _e("This page is for all loged in users they can access only when they are loged in. This page can be accessed by all types or level of users when they are loged in."); ?></p>
				</div>
			</div>
			<!-- End Basic Buttons -->
		</div>

		<div class="col-xl-12">
			<!-- Basic Buttons -->
			<div class="widget has-shadow">
				<div class="widget-header bordered no-actions d-flex align-items-center">
					<h4><?php _e("How to make a page accessable to all loged in users."); ?></h4>
				</div>
				<div class="widget-body">
					<p><?php _e("You can use the following code in start of your document that will make your page to accessable all loged in users but only when they are signed in."); ?></p>
<pre class="code php">
&lt;?php
	include('system_load.php');
	//This loads system.
	
	authenticate_user('all');
?&gt;</pre>
				</div>
			</div>
			<!-- End Basic Buttons -->
		</div>

		<div class="col-xl-12">
			<!-- Basic Buttons -->
			<div class="widget has-shadow">
				<div class="widget-header bordered no-actions d-flex align-items-center">
					<h4><?php _e("How to make more subscriber pages"); ?></h4>
				</div>
				<div class="widget-body">
				<p><?php _e("You can put the following code in top of any page that will become a subscriber accessable page. Note: admin can access all pages of all levels."); ?></p>
<pre class="code php">
&lt;?php
	include('system_load.php');
	//This loads system.
	
	authenticate_user('subscriber');
?&gt;		
</pre>
				</div>
			</div>
			<!-- End Basic Buttons -->
		</div>

		<div class="col-xl-12">
			<!-- Basic Buttons -->
			<div class="widget has-shadow">
				<div class="widget-header bordered no-actions d-flex align-items-center">
					<h4><?php _e("How to make more admin pages"); ?></h4>
				</div>
				<div class="widget-body">
				<p><?php _e("If you want to make a page only accessible for admin users then its very easy just add admin in user authentication function like below.."); ?></p>
<pre class="code php">
&lt;?php
	include('system_load.php');
	//This loads system.
	
	authenticate_user('admin');
?&gt;		
</pre>
				</div>
			</div>
			<!-- End Basic Buttons -->
		</div>

		<div class="col-xl-12">
			<!-- Basic Buttons -->
			<div class="widget has-shadow">
				<div class="widget-header bordered no-actions d-flex align-items-center">
					<h4><?php _e("Partial Access"); ?></h4>
				</div>
				<div class="widget-body">
					<p><?php _e("Partial access can be used to show some parts of a page for different level of users. For example if you want to include a file different for admin users and different for all other users you can do this using partial access function."); ?></p>
<pre class="code php">
&lt;?php if(partial_access('admin')): ?&gt;	
	&lt;p&gt;<?php _e("You are Admin."); ?>&lt;/p&gt;
&lt;?php elseif(partial_access('subscriber')): ?&gt;
	&lt;p&gt;<?php _e("You are Subscriber."); ?>&lt;/p&gt;
&lt;?php elseif(partial_access('all')): ?&gt;
	&lt;p&gt;<?php _e("You are loged in user."); ?>&lt;/p&gt;
&lt;?php else: ?&gt; 
	&lt;p&gt;<?php _e("You are not loged in user."); ?>&lt;/p&gt;
&lt;?php endif; ?&gt;
</pre>					
					<h2><?php _e("Working Example below of above code"); ?></h2>
					<?php if(partial_access('admin')): ?>	
						<h3><?php _e("You are Admin."); ?></h3>
					<?php elseif(partial_access('subscriber')): ?>
						<h3><?php _e("You are Subscriber."); ?></h3>
					<?php elseif(partial_access('all')): ?>
						<h3><?php _e("You are loged in user."); ?></h3>
					<?php else: ?> 
						<h3><?php _e("You are not loged in user."); ?></h3>
					<?php endif; ?>
				</div>
			</div>
			<!-- End Basic Buttons -->
		</div>
	</div><!-- Row /-->

<div class="clearfix"></div>
<?php
	require_once("lib/includes/footer.php");