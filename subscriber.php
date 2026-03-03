<?php
	require_once("lib/system_load.php");
	//Including this file we load system.
	
	//This loads system.
	authenticate_user('subscriber');
	
	$page_title = _('Subscriber Level Users And Admin users Can access this page only!');
	require_once('lib/includes/header.php');
?>
<div class="row flex-row">
	<div class="col-xl-12">
		<!-- Basic Buttons -->
		<div class="widget has-shadow">
			<div class="widget-header bordered no-actions d-flex align-items-center">
				<h4><?php _e("How user level pages works!"); ?></h4>
			</div>
			<div class="widget-body">
				<p><?php _e("In sidebar menu Dashboard would be linked to level page automatically. This is subscriber page and dashboard link in sidebar is linked to subscriber.php from database. You can create more user levels as per your needs and create pages like this which are only accessible by admin users and their level access holders. Non loged in users would be redirected to login page."); ?></p>
				<p><?php _e("This is a default page for subscriber user level, Subscriber user level is deafault level on registration. This page is only accessable when user is signed in and his access level is subscriber. If you are loged in as admin you still can see this page."); ?></p>
			</div>
		</div>
		<!-- End Basic Buttons -->
	</div><!-- Column Ends /-->

	<div class="col-xl-12">
			<!-- Basic Buttons -->
			<div class="widget has-shadow">
				<div class="widget-header bordered no-actions d-flex align-items-center">
					<h4><?php _e("How to make more subscriber pages or other level pages"); ?></h4>
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
</div><!-- Row ends /-->

<!--footer-->
<?php 
	require_once('lib/includes/footer.php');