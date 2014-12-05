<?php
/**
* @copyright (C) 2013 iJoomla, Inc. - All rights reserved.
* @license GNU General Public License, version 2 (http://www.gnu.org/licenses/gpl-2.0.html)
* @author iJoomla.com <webmaster@ijoomla.com>
* @url https://www.jomsocial.com/license-agreement
* The PHP code portions are distributed under the GPL license. If not otherwise stated, all images, manuals, cascading style sheets, and included JavaScript *are NOT GPL, and are released under the IJOOMLA Proprietary Use License v1.0
* More info at https://www.jomsocial.com/license-agreement
*/
defined('_JEXEC') or die();
?>
<style type="text/css">
	code
	{
		font-family: "andale mono", consolas, monaco, "lucida console", "courier new", courier, monospace; padding: 2px; background: #f5f5f5; border: 1px solid #ddd; border-radius: 3px;
	}
	table.doc
	{
		border-collapse: collapse; border-spacing: 0; width: 100%;
	}
	table.doc,
	table.doc th,
	table.doc td
	{
		border: 1px solid #ccc;
	}
	table.doc th,
	table.doc td
	{
		padding: 10px;
	}
</style>






<div class="cAlert">
	<strong>Alert title</strong>
	Best check yo self, you're not looking too good. Nulla vitae elit libero, a pharetra augue. Praesent commodo cursus magna, vel scelerisque nisl consectetur et.
</div>

<div class="cAlert alert-info">
	<strong>Alert title</strong>
	Best check yo self, you're not looking too good. Nulla vitae elit libero, a pharetra augue. Praesent commodo cursus magna, vel scelerisque nisl consectetur et.
</div>

<div class="cAlert alert-error">
	<strong>Alert title</strong>
	Best check yo self, you're not looking too good. Nulla vitae elit libero, a pharetra augue. Praesent commodo cursus magna, vel scelerisque nisl consectetur et.
</div>

<div class="cAlert alert-success">
	<strong>Alert title</strong>
	Best check yo self, you're not looking too good. Nulla vitae elit libero, a pharetra augue. Praesent commodo cursus magna, vel scelerisque nisl consectetur et.
</div>






<h2 class="cTitle">Horizontal Form</h2>

<ul class="cFormList cFormHorizontal cResetList">
	<li>
		<label class="form-label">Email :</label>

		<div class="form-field">
		    <input type="text" class="input text width-300" placeholder="Email">
			<span class="form-helper">Please insert your email here.</span>
		</div>
	</li>

	<li class="invalid">
		<label class="form-label">Password :</label>

		<div class="form-field">
		    <input type="password" class="input password width-300" placeholder="Password">
			<span class="form-helper">Don't forget your password.</span>
		</div>
	</li>

	<li>
		<label class="form-label">Description :</label>

		<div class="form-field">
		    <textarea rows="3" class="input textarea width-300"></textarea>
			<span class="form-helper">This is a form helper</span>
		</div>
	</li>

	<li class="invalid">
		<label class="form-label">Description :</label>

		<div class="form-field">
		    <select class="input select width-300">
				<option>Arsenal</option>
				<option>Chelsea</option>
				<option>Liverpool</option>
				<option>Manchester United</option>
			</select>
		</div>
	</li>

	<li class="has-seperator">
		<label class="form-label">Pick :</label>

		<div class="form-field">
			<label class="label-radio">
				<input class="input radio" type="radio" name="optionsRadios" id="optionsRadios1" value="option1" checked>
				The border seperator is cool.
			</label>
			<label class="label-radio">
				<input class="input radio" type="radio" name="optionsRadios" id="optionsRadios2" value="option2">
				Nothing special.
			</label>
		</div>
	</li>

	<li class="has-seperator">
		<label class="form-label">Tick :</label>

		<div class="form-field">
			<label class="label-checkbox">
				<input class="input checkbox" type="checkbox" value="">
				Option one is this and that—be sure to include why it's great
			</label>
		</div>
	</li>

	<li class="form-action has-seperator">
		<div class="form-field">
			<input class="button" type="submit" value="Submit">
		</div>
	</li>
</ul>






<h2 class="cTitle">Vertical Form</h2>

<ul class="cFormList cFormVertical cResetList">
	<li>
		<label class="form-label">Email :</label>

		<div class="form-field">
		    <input type="text" class="input width-300" placeholder="Email">
			<span class="form-helper">Please insert your email here.</span>
		</div>
	</li>

	<li>
		<label class="form-label">Password :</label>

		<div class="form-field">
		    <input type="password" class="input width-300" placeholder="Password">
			<span class="form-helper">Don't forget your password.</span>
		</div>
	</li>

	<li>
		<label class="form-label">Description :</label>

		<div class="form-field">
		    <textarea rows="3" class="input textarea width-300"></textarea>
			<span class="form-helper">This is a form helper</span>
		</div>
	</li>

	<li>
		<label class="form-label">Description :</label>

		<div class="form-field">
		    <select class="input select width-300">
				<option>Arsenal</option>
				<option>Chelsea</option>
				<option>Liverpool</option>
				<option>Manchester United</option>
			</select>
		</div>
	</li>

	<li class="has-seperator">
		<label class="form-label">Pick :</label>

		<div class="form-field">
			<label class="label-radio">
				<input class="input radio" type="radio" name="optionsRadios" id="optionsRadios1" value="option1" checked>
				The border seperator is cool.
			</label>
			<label class="label-radio">
				<input class="input radio" type="radio" name="optionsRadios" id="optionsRadios2" value="option2">
				Nothing special.
			</label>
		</div>
	</li>

	<li class="has-seperator">
		<label class="form-label">Tick :</label>

		<div class="form-field">
			<label class="label-checkbox">
				<input class="input checkbox" type="checkbox" value="">
				Option one is this and that—be sure to include why it's great
			</label>
		</div>
	</li>

	<li class="form-action has-seperator">
		<div class="form-field">
			<input class="button" type="submit" value="Submit">
		</div>
	</li>
</ul>

<h2>Tabs and navigation</h2>
<h3>Tabs</h3>
	<div class="cTabsBar clearfull">
		<ul class="cPageTabs cResetList cFloatedList clearfix">
			<li id="cTab-0" class="">
				<a href="javascript:void(0)">Tab 1</a>
			</li>

			<li class="" id="cTab-1">
				<a href="javascript:void(0)">Tab 2</a>
			</li>

			<li class="cTabDisabled" id="cTab-2">
				<a href="javascript:void(0)">Disabled tab</a>
			</li>

			<li id="cTab-3" class="cTabCurrent">
				<a href="javascript:void(0)">Active tab</a>
			</li>
		</ul>
	</div>


<h3>Filter</h3>
<div class="cFilter clearfull">
    <ul class="filters sort-a cResetList cFloatedList">
        <li class="filter active">
            <a href="#">Latest Groups</a>
        </li>
        <li class="filter">
            <a href="#">Alphabetical</a>
        </li>
        <li class="filter">
            <a href="#">Most Active</a>
        </li>
    </ul>
</div>



















<div class="cLayout" style="padding-top:150px; margin-top: 150px; border-top:1px solid #ddd">
	<h2>Component Layout</h2>


	<div class="cAlert alert-info"><code>.cLayout</code></div>

	<div class="cSidebar">

		<div class="cAlert"><code>.cSidebar</code></div>


		<div class="cModule app-box">
			<h3 class="app-box-header cResetH">Module Title</h3>
			<div class="app-box-content">
				Module content here
			</div>
			<div class="app-box-footer">
				<a href="#">Another LInk</a>
			</div>
		</div>


		<div class="cModule app-box">
			<h3 class="app-box-header cResetH">Module Link Listing</h3>
			<div class="app-box-content">
				<ul class="app-box-list for-menu cResetList">
					<li>
						<i class="com-icon-xxx"></i>
						<a href="#">Another Link</a>
					</li>
					<li>
						<i class="com-icon-xxx"></i>
						<a href="#">Another Link</a>
					</li>
					<li>
						<i class="com-icon-xxx"></i>
						<a href="#">Another Link</a>
					</li>
				</ul>
			</div>
		</div>


		<div class="cModule app-box control-admin">
			<h3 class="app-box-header cResetH">Module Link Listing for Admin</h3>
			<div class="app-box-content">
				<ul class="app-box-list for-menu cResetList">
					<li>
						<i class="com-icon-xxx"></i>
						<a href="#">Another Link</a>
					</li>
					<li>
						<i class="com-icon-xxx"></i>
						<a href="#">Another Link</a>
					</li>
					<li>
						<i class="com-icon-xxx"></i>
						<a href="#">Another Link</a>
					</li>
				</ul>
			</div>
		</div>



		<div class="cModule app-box">
			<h3 class="app-box-header cResetH">Module Listing Videos</h3>
			<div class="app-box-content">
				<ul class="cThumbsList cResetList clearfix">
					<li>
						<a href="#">
							<img src="http://placekitten.com/100/100" class="cAvatar cMediaAvatar" />
						</a>
					</li>
					<li>
						<a href="#">
							<img src="http://placekitten.com/100/100" class="cAvatar cMediaAvatar" />
						</a>
					</li>
					<li>
						<a href="#">
							<img src="http://placekitten.com/100/100" class="cAvatar cMediaAvatar" />
						</a>
					</li>
					<li>
						<a href="#">
							<img src="http://placekitten.com/100/100" class="cAvatar cMediaAvatar" />
						</a>
					</li>
					<li>
						<a href="#">
							<img src="http://placekitten.com/100/100" class="cAvatar cMediaAvatar" />
						</a>
					</li>
					<li>
						<a href="#">
							<img src="http://placekitten.com/100/100" class="cAvatar cMediaAvatar" />
						</a>
					</li>
				</ul>
			</div>
			<div class="app-box-footer">
				<a href="#">Another LInk</a>
			</div>
		</div>

		<div class="cModule app-box">
			<h3 class="app-box-header cResetH">Module Listing Photos</h3>
			<div class="app-box-content">
				<ul class="cThumbsList cResetList clearfix">
					<li>
						<a href="#">
							<img src="http://placekitten.com/120/120" class="cAvatar cMediaAvatar" />
						</a>
					</li>
					<li>
						<a href="#">
							<img src="http://placekitten.com/120/120" class="cAvatar cMediaAvatar" />
						</a>
					</li>
					<li>
						<a href="#">
							<img src="http://placekitten.com/120/120" class="cAvatar cMediaAvatar" />
						</a>
					</li>
				</ul>
			</div>
			<div class="app-box-footer">
				<a href="#">Another LInk</a>
			</div>
		</div>
	</div>

	<div class="cMain">
		<div class="cAlert"><code>.cMain</code></div>

		<div class="cGridFluid">

			<h2>Fluid Grid system</h2>
			<p>Follow bootstrap grid system, but under community-wrap wrapper. Always use <code>.row-fluid</code> instead.</p>
			<p>Don't forget to wrap all your <code>.row-fluid</code> with <code>.cGridFluid</code>. Please inspect the structure for your references.</p>
			<div class="row-fluid">
				<div class="span6">
					<div class="cAlert alert-info"><code>.span6</code></div>
				</div>

				<div class="span6">
					<div class="cAlert alert-info"><code>.span6</code></div>
				</div>
			</div>

			<div class="row-fluid">
				<div class="span4">
					<div class="cAlert alert-info"><code>.span4</code></div>
				</div>

				<div class="span4">
					<div class="cAlert alert-info"><code>.span4</code></div>
				</div>

				<div class="span4">
					<div class="cAlert alert-info"><code>.span4</code></div>
				</div>
			</div>

			<div class="row-fluid">
				<div class="span3">
					<div class="cAlert alert-info"><code>.span3</code></div>
				</div>

				<div class="span9">
					<div class="cAlert alert-info"><code>.span9</code></div>
				</div>
			</div>

			<div class="row-fluid">
				<div class="span9">
					<div class="cAlert alert-info"><code>.span9</code></div>
				</div>

				<div class="span3">
					<div class="cAlert alert-info"><code>.span3</code></div>
				</div>
			</div>

			<hr class="cSeperator" />

			<h2>Stream Pattern</h2>
				<ul class="cStreamList cResetList" >
				<li id="" class="stream">
					<a class="cStream-Avatar cFloat-L" href="#">
						<img class="cAvatar" data-author="" src="<?php echo JURI::root(); ?>/components/com_community/assets/user_thumb.png">
					</a>

					<div class="joms-stream-content">
						<div class="cStream-Headline">
							<a href="#" class="cStream-Author">Actor</a> A did something funny
						</div>

						<div class="cStream-Attachment">
							This would be his custom message
						</div>

						<div class="cStream-Attachment">
							<div class="cStream-Photo">
								<div class="cStream-PhotoRow row-fluid">
									<div class="span3">
										<a class="cPhoto-Thumb" href="#"><img src="http://placekitten.com/120/120" alt="photo"></a>
									</div>
								</div>
							</div>
						</div>
						<div class="cStream-Attachment">
							<div class="cStream-Quote">
								Single media attachement
							</div>
						</div>

						<div class="cStream-Attachment">
							<div class="cStream-Photo">
								<div class="cStream-PhotoRow row-fluid">
									<div class="span3">
										<a class="cPhoto-Thumb" href="#"><img src="http://placekitten.com/120/120" alt="photo"></a>
									</div>
									<div class="span3">
										<a class="cPhoto-Thumb" href="#"><img src="http://placekitten.com/120/120" alt="photo"></a>
									</div>
									<div class="span3">
										<a class="cPhoto-Thumb" href="#"><img src="http://placekitten.com/120/120" alt="photo"></a>
									</div>
									<div class="span3">
										<a class="cPhoto-Thumb" href="#"><img src="http://placekitten.com/120/120" alt="photo"></a>
									</div>
								</div>
							</div>
						</div>

						<div class="cStream-Attachment">
							<div class="cStream-Quote">
								Multiple media attachement
							</div>
						</div>

						<div class="cStream-Attachment">
							<div class="cStream-Video clearfix">
								<a href="javascript:joms.walls.showVideoWindow('9')" class="cVideo-Thumb cFloat-L">
									<img src="http://placekitten.com/120/90" alt="Ducati 1199 Panigale Review">
									<b>06:02</b>
								</a>

								<div class="cVideo-Content">
									<b class="cVideo-Title">
										<a href="javascript:joms.walls.showVideoWindow('9')">Ducati 1199 Panigale Review</a>
									</b>
									<div class="cVideo-Desc">
										Luke compares the sexy red number to having an Italian girlfriend.  To see more vids and reviews like this check out our TV show Bike World,  it's...
									</div>
								</div>
							</div>
						</div>

						<div class="cStream-Attachment">
							<div class="cStream-Quote">
								Some videos attachment
							</div>
						</div>

						<div class="cStream-Actions clearfull small">
							<i class="cStream-Icon com-icon-profile "></i>
							<!-- Show likes -->
							<span>
								<a href="#like" id="like_id340">Like</a>
							</span>

							<!-- Show if it is explicitly allowed: -->
							<span><a onclick="joms.miniwall.show('340');return false;" href="javascript:void(0);">Comment</a></span>
							<span><a title="Delete" class="newsfeed-location" href="#deletePost">Delete</a></span>

							<span>11 hours 39 minutes  ago</span>

						</div>
					</div>
				</li>

				<li data-streamid="213" class="stream feed-events.attend" id="events.attend-newsfeed-item-213">
					<div class="cStream-Content">
					<i class="joms-icon-users"></i>
					<a href="/~azrul/jomsocial/index.php/en/jomsocial/634julia/profile" class="cStream-Author">Julia</a>,
					<a href="/~azrul/jomsocial/index.php/en/jomsocial/637-jack/profile" class="cStream-Author">Jack</a>,
					<a href="/~azrul/jomsocial/index.php/en/jomsocial/635-jeniffer/profile" class="cStream-Author">Jeniffer</a>,
					<a href="/~azrul/jomsocial/index.php/en/jomsocial/636jamal/profile" class="cStream-Author">Jamal</a>
						-
			 			is attending <a href="/~azrul/jomsocial/index.php/en/jomsocial/events/viewevent/1-new-year-party">New year party</a>.</div>
				</li>

			</ul>

			<hr class="cSeperator" />
			<h2>Buttons</h2>
			<p>Button styles can be applied to anything with the <code>.btn</code> class applied. However, typically you'll want to apply these to only <code>&lt;a&gt;</code> and <code>&lt;button&gt;</code> elements for the best rendering.</p>
			<p>&nbsp;</p>


			<table class="doc">
				<tr>
					<td width="150">
						<a href="#" class="cButton">Button</a>
					</td>
					<td width="320">
						<code>cButton</code>
					</td>
					<td>
						Standard button
					</td>
				</tr>
				<tr>
					<td>
						<a href="#" class="cButton cButton-Blue">Button</a>
					</td>
					<td>
						<code>cButton cButton-Blue</code>
					</td>
					<td>
						Blue button
					</td>
				</tr>
				<tr>
					<td>
						<a href="#" class="cButton cButton-Green">Button</a>
					</td>
					<td>
						<code>cButton cButton-Green</code>
					</td>
					<td>
						Green button
					</td>
				</tr>
				<tr>
					<td>
						<a href="#" class="cButton cButton-Black">Button</a>
					</td>
					<td>
						<code>cButton cButton-Black</code>
					</td>
					<td>
						Black button
					</td>
				</tr>
				<tr>
					<td width="150">
						<a href="#" class="cButton cButton-Large">Button</a>
					</td>
					<td width="210">
						<code>cButton cButton-Large</code>
					</td>
					<td>
						Standard button
					</td>
				</tr>
				<tr>
					<td>
						<a href="#" class="cButton cButton-Blue cButton-Large">Button</a>
					</td>
					<td>
						<code>cButton cButton-Blue cButton-Large</code>
					</td>
					<td>
						Blue button
					</td>
				</tr>
				<tr>
					<td>
						<a href="#" class="cButton cButton-Green cButton-Large">Button</a>
					</td>
					<td>
						<code>cButton cButton-Green cButton-Large</code>
					</td>
					<td>
						Green button
					</td>
				</tr>
				<tr>
					<td>
						<a href="#" class="cButton cButton-Black cButton-Large">Button</a>
					</td>
					<td>
						<code>cButton cButton-Black cButton-Large</code>
					</td>
					<td>
						Black button
					</td>
				</tr>
				<tr>
					<td width="150">
						<a href="#" class="cButton cButton-Small">Button</a>
					</td>
					<td width="210">
						<code>cButton cButton-Small</code>
					</td>
					<td>
						Standard button
					</td>
				</tr>
				<tr>
					<td>
						<a href="#" class="cButton cButton-Blue cButton-Small">Button</a>
					</td>
					<td>
						<code>cButton cButton-Blue cButton-Small</code>
					</td>
					<td>
						Blue button
					</td>
				</tr>
				<tr>
					<td>
						<a href="#" class="cButton cButton-Green cButton-Small">Button</a>
					</td>
					<td>
						<code>cButton cButton-Green cButton-Small</code>
					</td>
					<td>
						Green button
					</td>
				</tr>
				<tr>
					<td>
						<a href="#" class="cButton cButton-Black cButton-Small">Button</a>
					</td>
					<td>
						<code>cButton cButton-Black cButton-Small</code>
					</td>
					<td>
						Black button
					</td>
				</tr>
			</table>
		</div>
	</div>

</div>
