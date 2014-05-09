<?php
/**
 * Represents the view for the administration dashboard.
 *
 * This includes the header, options, and other information that should provide
 * The User Interface to the end user.
 *
 * @package   Orion
 * @author    seishynon <sshnn@outlook.com>
 * @license   GPL-2.0+
 * @link      http://sshnn.tumblr.com/
 * @copyright 2014 seishynon
 */
?>
<div id="orion-layout" class="wrap">
	<h2 class="orion-page-title"><a href="#"><?php echo esc_html( get_admin_page_title() ); ?></a></h2>
	<div id="orion-container"></div>
</div>

<!-- Templates -->
<script type="text/template" id="orion-dash-template">
	<div data-subview="serverStatus"></div>

	<div>
		<div class="orion-postbox-container">
			<div data-subview="playersList"></div>
		</div>
	</div>
</script>

<script type="text/template" id="orion-dash-server-status-template">
	<div class="welcome-panel">
		<a class="welcome-panel-close" href="#"><span class="dashicons dashicons-admin-settings"></span><?=__( 'Settings', 'orion' )?></a>
		<div class="welcome-panel-content">
			<h3><%= name %></h3>
			<p class="about-description"><?=__( 'Running <%= version %> with <%= plugins_count %> plugins', 'orion' )?></p>
			<div class="welcome-panel-column-container">
				<div class="welcome-panel-column">
					<h4><?=__( 'Loaded Worlds', 'orion' )?></h4>
					<ul class="orion-list orion-list-worlds">
						<% _.each(worlds_names, function(world_name) {
							%><li><%= world_name %></li><%
						}); %>
					</ul>
				</div>
				<div class="welcome-panel-column">
					<h4><?=__( 'Memory Usage', 'orion' )?></h4>
					<canvas id="orion-memory-usage-chart" class="orion-graph" width="200" height="200"></canvas>
					<div class="orion-fraction">
						<div><%= memory_used %> mb</div>
						<div><%= memory_total %> mb</div>
					</div>
				</div>
				<div class="welcome-panel-column welcome-panel-last">
					<h4><?=__( 'Connected players', 'orion' )?></h4>
					<canvas id="orion-players-chart" class="orion-graph" width="200" height="200"></canvas>
					<div class="orion-fraction">
						<div><%= players_online_count %></div>
						<div><%= players_online_limit %></div>
					</div>
				</div>
			</div>
		</div>
	</div>
</script>

<script type="text/template" id="orion-dash-players-list-template">
	<div class="postbox">
		<h3><span><?=__( 'Connected Players', 'orion' )?></span></h3>
		<div class="inside">
			<% if (players_online_count === 0) { %>
				<p class="orion-no-players"><?=__( 'Well, actually there is nobody on your server.', 'orion' )?></p>
			<% } else { %>
				<form class="orion-inline-form">
					<label for="orion-player-name"><?=__( 'Filter by name', 'orion' )?></label>
					<input type="text" name="orion-player-name" id="orion-player-name" class="text-regular">
				</form>
				<br class="clear">
				<ul class="orion-list orion-list-players">
				</ul>
			<% } %>
		</div>
	</div>
</script>

<script type="text/javascript" id="orion-players-list-item-template">
	<li><img class="orion-player-avatar" src="https://minotar.net/avatar/<%= player_name %>/34"/><%= player_name %><a href="#player/<%= player_name %>" class="button"><?=__( 'Manage Player', 'orion' )?></a></li>
</script>

<script type="text/template" id="orion-player-template">
	<div class="welcome-panel">
		<div class="welcome-panel-content">
			<div class="welcome-panel-column-container">
				<div class="welcome-panel-column" id="orion-player-details-container">
					<img class="orion-player-avatar" src="https://minotar.net/avatar/<%= name %>">
					<div class="orion-player-details">
						<h3><%= name %></h3>
						<p class="about-description orion-player-status"><% if(ip === 'offline') { %><?=__( 'Offline', 'orion')?><% } else { %><?=__('Connected from <%= ip %>', 'orion' )?><% } %></p>
					</div>
				</div>
				<div class="welcome-panel-column" id="orion-player-stats-container">
					<h4><?=__( 'Stats', 'orion' )?></h4>
					<ul class="orion-list">
						<li><?=__( 'Health', 'orion' )?> <span><strong><%= health %></strong>/20</span></li>
						<li><?=__( 'Food level', 'orion' )?> <span><strong><%= foodLevel %></strong>/20</span></li>
						<li><?=__( 'XP level', 'orion' )?> <span><strong><%= level %></strong></span></li>
						<% if(has_account === true) { %>
							<li><?=__( 'Balance', 'orion' )?> <span><strong><%= balance %></strong></span></li>
						<% } %>
					</ul>
				</div>
				<div class="welcome-panel-column welcome-panel-last" id="orion-player-misc-container">
					<h4><?=__( 'Miscellaneous', 'orion' )?></h4>
					<ul class="orion-list">
						<li class="wp-core-ui"><?=__( 'Gamemode', 'orion' )?>
							<span>
								<div class="button-group orion-gamemode-buttons">
									<button class="button<% if(gameMode === 0) {%> active<% } %>" value="0"><?=__( 'Normal', 'orion' )?></button>
									<button class="button<% if(gameMode === 1) {%> active<% } %>" value="1"><?=__( 'Creative', 'orion' )?></button>
								</div>
							</span>
						</li>
						<li><?=__( 'OP:', 'orion' )?>
							<strong><% if(op) { %><?=__( 'Yes', 'orion' )?><% } else { %><?=__( 'No', 'orion' )?><% } %></strong>
							<span>
								<button class="button" id="orion-op-player-button">
								<% if(op) { %><?=__( 'De-OP', 'orion' )?><% } else { %><?=__( 'OP', 'orion' )?><% } %>
								</button>
							</span>
						</li>
						<li><?=__( 'Whitelisted:', 'orion' )?>
							<strong><% if(whitelisted) { %><?=__( 'Yes', 'orion' )?><% } else { %><?=__( 'No', 'orion' )?><% } %></strong>
							<span>
								<button class="button" id="orion-whitelist-player-button">
									<% if(whitelisted) { %><?=__( 'Remove', 'orion' )?><% } else { %><?=__( 'Add', 'orion' )?><% } %>
								</button>
							</span>
						</li>
						<li><?=__( 'Banned:', 'orion' )?>
							<strong><% if(banned) { %><?=__( 'Yes', 'orion' )?><% } else { %><?=__( 'No', 'orion' )?><% } %></strong>
							<span>
								<button class="button" id="orion-kick-player-button">
									<?=__( 'Kick', 'orion' )?>
								</button>
								<button class="button" id="orion-ban-player-button">
									<% if(banned) { %><?=__( 'Un-ban', 'orion' )?><% } else { %><?=__( 'Ban', 'orion' )?><% } %>
								</button>
							</span>
						</li>
					</ul>
				</div>
			</div>
		</div>
	</div>
</script>

<script type="text/template" id="orion-world-template">
	<div class="welcome-panel">
		<div class="welcome-panel-content">
			<h3>Managing world: <%= name %></h3>
			<p class="about-description">Balh</p>
			<div class="welcome-panel-column-container">
				<div class="welcome-panel-column">
					<h4><?=__( 'Settings', 'orion' )?></h4>
					<form class="form">
						<ul class="orion-list">
							<li>
								<label for="difficulty-dropdown"><?=__( 'Difficulty', 'orion' )?></label>
								<span>
									<select name="difficulty-dropdown" id="difficulty-dropdown">
										<option value="0" <% if(difficulty === 0) { %> selected <% } %> ><?=__('Easy')?></option>
										<option value="1" <% if(difficulty === 1) { %> selected <% } %> ><?=__('Hard')?></option>
										<option value="2" <% if(difficulty === 2) { %> selected <% } %> ><?=__('Normal')?></option>
										<option value="3" <% if(difficulty === 3) { %> selected <% } %> ><?=__('Peaceful')?></option>
									</select>
								</span>
							</li>
							<li>
								<label><?=__('Enable PvP', 'orion')?></label>
								<span>
									<label><input type="radio" name="pvp" value="yes" <% if(isPVP) { %> checked <% } %> > <?=__( 'Yes', 'orion')?></label>
									<label><input type="radio" name="pvp" value="no" <% if(!isPVP) { %> checked <% } %> > <?=__( 'No', 'orion')?></label>
								</span>
							</li>
							<li><span><button class="button button-primary" id="orion-save-world-settings"><?=__( 'Save', 'orion' )?></button></span></li>
						</ul>
					</form>
				</div>
				<div class="welcome-panel-column">
					<h4><?=__( 'Connected players', 'orion' )?></h4>
				</div>
				<div class="welcome-panel-column welcome-panel-last">
					<h4><?=__( 'Weather', 'orion' )?></h4>
					<ul class="orion-weather-icon">
						<% if(hasStorm === true) { %>
							<li class="icon-cloud"></li><!--
						 --><li class="<% if(isThundering === true) { %>icon-thunder<% } else { %>icon-drizzle<% } %> <% if(time >= 0 && time < 12000) { %>icon-sunny<% } else { %>icon-night<% } %>"></li>	
						<% } else { %>
							<% if(time >= 0 && time < 3000) { %>
								<li class="icon-sunrise"></li>
							<% } else if(time >= 3000 && time < 9000) { %>
								<li class="icon-sun"></li>
							<% } else if(time >= 9000 && time < 12000) { %>
								<li class="icon-sunset"></li>
							<% } else { %>
								<li class="icon-moon"></li>
							<% } %>
						<% } %>
					</ul>
				</div>
			</div>
		</div>
	</div>
</script>

<script type="text/template" id="orion-request-failed-template">
	<div class="error">
		<p><?=__( 'An error occured. Please try again later.', 'orion' )?></p>
	</div>
</script>

<script type="text/template" id="orion-no-connection-template">
	<div class="error">
		<p><?=__( 'We couldn\'t establish a connection with your server.', 'orion' )?><br/>
		<?=__( 'Please check your credentials and your server.', 'orion' )?></p>
	</div>
</script>

<script type="text/template" id="orion-confirm-player-ban-template">
	<?=__( 'Are you sure you want to ban <%= name %> ?', 'orion' )?>
</script>

<script type="text/template" id="orion-prompt-player-ban-template">
	<?=__( 'Please specify a reason for this ban:', 'orion' )?>
</script>