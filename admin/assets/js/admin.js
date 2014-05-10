var orion = orion || {};

(function ($, orion) {
	"use strict";

	orion.Router = Backbone.Router.extend({
		layout: null,
		initialize: function(options) {
			this.layout = options.layout;
		},
		routes: {
			'': 'home',
			'player/:player_name': 'player',
			'manage-server(/config)': 'manageServerConfig',
			'manage-server/whitelist': 'manageServerWhitelist',
			'manage-server/blacklist': 'manageServerBlacklist',
			'manage-server/plugins': 'manageServerPlugins',
		},
		home: function() {
			var self = this;
			var serverModel;
			$.post(
				orion_data.ajax_url,
				{action: 'orion_get_server'},
				function(response) {
					serverModel = new orion.Server(response);
				}
			).then(function() {
				self.layout.renderView(new orion.DashView({
					model: serverModel
				}));
			});
		},
		player: function(player_name) {
			var self = this;
			var playerModel;
			$.post(
				orion_data.ajax_url,
				{
					action: 'orion_get_player',
					player_name: player_name
				},
				function(response) {
					playerModel = new orion.Player(response);
				}
			).then(function() {
				self.layout.renderView(new orion.PlayerView({
					model: playerModel
				}));
			});
		},
		manageServerConfig: function() {
			var self = this;
			var serverModel;
			$.post(
				orion_data.ajax_url,
				{action: 'orion_get_server'},
				function(response) {
					serverModel = new orion.Server(response);
				}
			).then(function() {
				self.layout.renderView(new orion.ServerConfigView({
					model: serverModel
				}));
			});
		},
		manageServerWhitelist: function() {
			var self = this;
			var playersList;
			$.post(
				orion_data.ajax_url,
				{action: 'orion_get_whitelisted_players'},
				function(response) {
					playersList = response;
				}
			).then(function() {
				self.layout.renderView(new orion.ServerWhitelistView({
					model: playersList
				}));
			});
		},
		manageServerBlacklist: function() {
			var self = this;
			var playersList;
			$.post(
				orion_data.ajax_url,
				{action: 'orion_get_blacklisted_players'},
				function(response) {
					playersList = response;
				}
			).then(function() {
				self.layout.renderView(new orion.ServerBlacklistView({
					model: playersList
				}));
			});
		},
		manageServerPlugins: function() {
			var self = this;
			var serverModel;
			$.post(
				orion_data.ajax_url,
				{action: 'orion_get_server'},
				function(response) {
					serverModel = new orion.Server(response);
				}
			).then(function() {
				self.layout.renderView(new orion.ServerPluginsView({
					model: serverModel
				}));
			});
		}
	});

	orion.Server = Backbone.Model.extend({});

	orion.ServerConfig = Backbone.Model.extend({});

	orion.Player = Backbone.Model.extend({});

	orion.LayoutView = Backbone.View.extend({
		el: '#orion-layout',
		nestedView: null,
		render: function() {
			this.$el.html(this.nestedView.render().el);
		},
		setView: function(newView) {
			if (this.nestedView !== null) {
				this.nestedView.remove();
			}
			this.nestedView = newView;
			this.nestedView.parent = this;
			return this;
		},
		renderView: function(newView) {
			this.setView(newView);
			this.$('#orion-container').html(this.nestedView.render().el);
			return this;
		}
	});

	orion.DashView = Backbone.View.extend({
		id: 'orion-dash',
		template: _.template($('#orion-dash-template').html()),
		parent: null,
		serverInterval: null,
		subviewCreators: {
			'serverStatus': function() {
				return new orion.DashServerStatusView({ parent: this });
			},
			'playersList': function() {
				return new orion.DashPlayersListView({ parent: this });
			}
		},
		initialize: function() {
			Backbone.Subviews.add(this);
			this.serverInterval = setInterval(this.updateServer, 5000, this);
			return this;
		},
		render: function() {
			if (this.model.get('name') !== null) {
				this.$el.html(this.template(this.model.attributes));
			} else {
				this.$el.html($('#orion-no-connection-template').html());
			}
			return this;
		},
		remove: function() {
			clearInterval(this.serverInterval);
			return this;
		},
		updateServer: function(self) {
			$.post(
				orion_data.ajax_url,
				{ action: 'orion_get_server' },
				function(response) {
					var newData = {};
					var oldData = self.model.attributes;
					_.each(response, function(value, key) {
						if(value !== oldData[key]) {
							newData[key] = value;
						}
					});
					self.model.set(newData);
				}
			);
		}
	});

	orion.DashServerStatusView = Backbone.View.extend({
		id: 'orion-dash-server-status',
		template: _.template($('#orion-dash-server-status-template').html()),
		charts: {},
		initialize: function(options) {
			this.parent = options.parent;
			this.model = this.parent.model;
			this.model.on('all', function(eventName) {
				if(
					(eventName === 'change:memory_used') ||
					(eventName === 'change:memory_free') ||
					(eventName === 'change:players_online_count') ||
					(eventName === 'change:players_online_limit')
				) {
					this.render();
				}
			}, this);
			return this;
		},
		// @todo: The view is rendered twice every 5 seconds, it should only be rendered once.
		render: function() {
			this.$el.html(this.template(this.model.attributes));
			this.charts.memoryUsage = new Chart(this.$('#orion-memory-usage-chart')[0].getContext('2d'))
					.Doughnut([
						{ value: this.model.get('memory_used'), color: '#428BCA' },
						{ value: this.model.get('memory_free'), color: '#ddd' }
					], {
						animation: false
					}
				);
				this.charts.players = new Chart(this.$('#orion-players-chart')[0].getContext('2d'))
					.Doughnut([
						{ value: this.model.get('players_online_count'), color: "#428BCA" },
						{ value: this.model.get('players_online_limit'), color: "#ddd" }
					], {
						animation: false
					});
			return this;
		}
	});

	orion.DashPlayersListView = Backbone.View.extend({
		id: 'orion-dash-players-list',
		template: _.template($('#orion-dash-players-list-template').html()),
		events: {
			'keyup #orion-player-name': 'filterList'
		},
		initialize: function(options) {
			this.parent = options.parent;
			this.model = this.parent.model;
			this.model.on('change:players_online_count', this.renderList, this);
			return this;
		},
		render: function() {
			this.$el.html(this.template(this.model.attributes));
			if(this.model.get('players_online_count') !== 0)
				this.renderList();
			return this;
		},
		renderList: function() {
			var listHtml = '';
			var filter = this.filter;
			console.log('filter' + filter)
			_.each(this.model.get('players_online_names'), function(value) {
				if(
					(filter === undefined) ||
					(filter === '') ||
					(value.toLowerCase().indexOf(filter) !== -1)
				) {
					console.log(value + ' contains the filter ' + filter);
					listHtml += _.template($('#orion-players-list-item-template').html())({ player_name: value });
				}
			});
			this.$('.orion-list-players').html(listHtml);
			return this;
		},
		filterList: function(e) {
			this.filter = ($(e.target).val() === undefined) ? '': $(e.target).val().toLowerCase();
			this.renderList();
			return this;
		}
	});

	orion.PlayerView = Backbone.View.extend({
		id: 'orion-player',
		template: _.template($('#orion-player-template').html()),
		playerInterval: null,
		events: {
			'click .orion-gamemode-buttons .button': 'toggleGamemode',
			'click #orion-op-player-button':         'toggleIt',
			'click #orion-whitelist-player-button':  'toggleIt',
			'click #orion-ban-player-button':        'toggleIt',
			'click #orion-kick-player-button':       'kickPlayer',
			'click #orion-player-send-message':      'sendMessage'
		},
		initialize: function() {
			this.playerInterval = setInterval(this.updatePlayer, 5000, this);
			this.model.on('change', this.render, this);
			return this;
		},
		render: function() {
			this.$el.html(this.template(this.model.attributes));
			return this;
		},
		remove: function() {
			this.model.off('change', this.render);
			clearInterval(this.playerInterval);
			return this;
		},
		updatePlayer: function(self) {
			$.post(
				orion_data.ajax_url,
				{
					action: 'orion_get_player',
					player_name: self.model.get('name')
				},
				function(response) {
					console.log(response);
					self.model.set(response);
				}
			)
			return this;
		},
		toggleGamemode: function(e) {
			var self = this;
			$.post(
				orion_data.ajax_url,
				{
					action: 'orion_update_player_gamemode',
					player_name: this.model.get('name'),
					gamemode_id: $(e.target).val()
				},
				function(response) {
					if(!response.is_success) {
						self.$el.prepend($('#orion-request-failed-template').html());
					} else {
						self.updatePlayer(self);
					}
				}
			);
			return this;
		},
		toggleIt: function(e) {
			var self = this;
			var toggle = $(e.target).attr('id').replace('orion-', '').replace('-player-button', '');
			if(toggle === 'op') {
				var is_it = this.model.get(toggle);
			} else if(toggle === 'ban') {
				var is_it = this.model.get('banned');
			} else if(toggle === 'whitelist') {
				var is_it = this.model.get('whitelisted');
			}
			$.post(
				orion_data.ajax_url,
				{
					action: 'orion_toggle_player_it',
					is_it: is_it,
					player_name: this.model.get('name'),
					toggle: toggle
				},
				function(response) {
					if(!response.success) {
						self.$el.prepend($('#orion-request-failed-template').html());
					} else {
						self.updatePlayer(self);
					}
				}
			);
			return this;
		},
		kickPlayer: function(e) {
			var self = this;
			$.post(
				orion_data.ajax_url,
				{
					action: 'orion_kick_player',
					player_name: this.model.get('name')
				}
			).then(function() {
				self.updatePlayer(self);
			});
			return this;
		},
		sendMessage: function(e) {
			e.preventDefault();
			var self = this;
			var message = this.$('#orion-player-message').val();

			if(message === '') {
				return;
			}

			$.post(
				orion_data.ajax_url,
				{
					action: 'orion_send_message',
					player_name: this.model.get('name'),
					message: message
				}
			).then(function() {
				self.$('#orion-player-message').val(null);
			});
			return this;
		}
	});

	orion.ServerConfigView = Backbone.View.extend({
		id: 'orion-server-config',
		template: _.template($('#orion-server-config-template').html()),
		initialize: function() {
			return this;
		},
		render: function() {
			this.$el.html(this.template(this.model.attributes));
			return this;
		}
	});

	orion.ServerWhitelistView = Backbone.View.extend({
		id: 'orion-server-whitelist',
		template: _.template($('#orion-server-whitelist-template').html()),
		events: {
			'keyup #orion-player-name-filter': 'filterList',
			'submit #orion-whitelist-form': 'addPlayer',
			'click .unwhitelist': 'removePlayer'
		},
		initialize: function() {
			return this;
		},
		render: function() {
			this.$el.html(this.template(this.model.attributes));
			this.renderList();
			return this;
		},
		renderList: function() {
			var listHtml = '';
			var filter = this.filter;
			console.log('filter' + filter)
			_.each(this.model, function(value) {
				if(
					(filter === undefined) ||
					(filter === '') ||
					(value.toLowerCase().indexOf(filter) !== -1)
				) {
					listHtml += _.template($('#orion-players-whitelist-item-template').html())({ player_name: value });
				}
			});
			this.$('.orion-list-players').html(listHtml);
			return this;
		},
		filterList: function(e) {
			this.filter = ($(e.target).val() === undefined) ? '': $(e.target).val().toLowerCase();
			return this.renderList();
		},
		addPlayer: function(e) {
			e.preventDefault();
			var player_name = (this.$('#orion-player-name').val() === undefined) ? '': this.$('#orion-player-name').val();
			if(player_name === '') {
				return this;
			} else if(this.model.indexOf(player_name) !== -1) {
				this.$el.prepend(_.template($('#orion-player-already-whitelisted-template').html())({player_name: player_name}));
				this.$('#orion-player-name').val(null);
				return this;
			} else {
				var self = this;
				$.post(
					orion_data.ajax_url,
					{
						action: 'orion_toggle_player_it',
						player_name: player_name,
						toggle: 'whitelist',
						is_it: false
					},
					function(response) {
						if(!response.success) {
							console.log(response)
							self.$el.prepend($('#orion-request-failed-template').html());
						} else {
							self.model.unshift(player_name);
							self.$('#orion-player-name').val(null);
							self.renderList();
						}
					}
				)
			}
			return this;
		},
		removePlayer: function(e) {
			e.preventDefault();
			var self = this;
			var player_name = $(e.target).data('name');
			$.post(
				orion_data.ajax_url,
				{
					action: 'orion_toggle_player_it',
					player_name: player_name,
					toggle: 'whitelist',
					is_it: true
				},
				function(response) {
					if(!response.success) {
						console.log(response)
						self.$el.prepend($('#orion-request-failed-template').html());
					} else {
						// Delete the player from the whitelist array
						self.model.splice(self.model.indexOf(player_name), 1);
						self.renderList();
					}
				}
			);
			return this;
		}
	});

	orion.ServerBlacklistView = Backbone.View.extend({
		id: 'orion-server-blacklist',
		template: _.template($('#orion-server-blacklist-template').html()),
		events: {
			'keyup #orion-player-name-filter': 'filterList',
			'submit #orion-blacklist-form': 'addPlayer',
			'click .pardon': 'removePlayer'
		},
		initialize: function() {
			return this;
		},
		render: function() {
			this.$el.html(this.template(this.model.attributes));
			this.renderList();
			return this;
		},
		renderList: function() {
			var listHtml = '';
			var filter = this.filter;
			console.log('filter' + filter)
			_.each(this.model, function(value) {
				if(
					(filter === undefined) ||
					(filter === '') ||
					(value.toLowerCase().indexOf(filter) !== -1)
				) {
					listHtml += _.template($('#orion-players-blacklist-item-template').html())({ player_name: value });
				}
			});
			this.$('.orion-list-players').html(listHtml);
			return this;
		},
		filterList: function(e) {
			this.filter = ($(e.target).val() === undefined) ? '': $(e.target).val().toLowerCase();
			return this.renderList();
		},
		addPlayer: function(e) {
			e.preventDefault();
			var player_name = (this.$('#orion-player-name').val() === undefined) ? '': this.$('#orion-player-name').val();
			if(player_name === '') {
				return this;
			} else if(this.model.indexOf(player_name) !== -1) {
				this.$el.prepend(_.template($('#orion-player-already-blacklisted-template').html())({player_name: player_name}));
				this.$('#orion-player-name').val(null);
				return this;
			} else {
				var self = this;
				$.post(
					orion_data.ajax_url,
					{
						action: 'orion_toggle_player_it',
						player_name: player_name,
						toggle: 'ban',
						is_it: false
					},
					function(response) {
						if(!response.success) {
							console.log(response)
							self.$el.prepend($('#orion-request-failed-template').html());
						} else {
							self.model.unshift(player_name);
							self.$('#orion-player-name').val(null);
							self.renderList();
						}
					}
				)
			}
			return this;
		},
		removePlayer: function(e) {
			e.preventDefault();
			var self = this;
			var player_name = $(e.target).data('name');
			$.post(
				orion_data.ajax_url,
				{
					action: 'orion_toggle_player_it',
					player_name: player_name,
					toggle: 'ban',
					is_it: true
				},
				function(response) {
					if(!response.success) {
						console.log(response)
						self.$el.prepend($('#orion-request-failed-template').html());
					} else {
						// Delete the player from the blacklist array
						self.model.splice(self.model.indexOf(player_name), 1);
						self.renderList();
					}
				}
			);
			return this;
		}
	});

	orion.ServerPluginsView = Backbone.View.extend({
		id: 'orion-server-plugins',
		template: _.template($('#orion-server-plugins-template').html()),
		initialize: function() {
			return this;
		},
		render: function() {
			this.$el.html(this.template(this.model.attributes));
			return this;
		}
	});

	orion.routerins = new orion.Router({ layout: new orion.LayoutView() });
	Backbone.history.start();

}(jQuery, orion));