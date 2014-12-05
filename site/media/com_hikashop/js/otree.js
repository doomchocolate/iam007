/**
 * @package    HikaShop for Joomla!
 * @version    2.3.0
 * @author     hikashop.com
 * @copyright  (C) 2010-2014 HIKARI SOFTWARE. All rights reserved.
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
/*
	oTree : Obscurelighty Project ( http://www.obscurelighty.com/ )
	Author: Jerome GLATIGNY <jerome@obscurelighty.com>
	Copyright (C) 2010-2014  Jerome GLATIGNY

	This file is part of Obscurelighty.

	Obscurelighty is free software: you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation, either version 3 of the License, or
	(at your option) any later version.

	Obscurelighty is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with Obscurelighty.  If not, see <http://www.gnu.org/licenses/>.

	The open source license for Obscurelighty and its modules permits you to use the
	software at no charge under the condition that if you use in an application you
	redistribute, the complete source code for your application must be available and
	freely redistributable under reasonable conditions. If you do not want to release the
	source code for your application, you may purchase a proprietary license from his author.
*/

/** oTree
 * version: 0.9.7
 * release date: 2012-09-09
 */
(function(){
	window.oTrees = [];

	/** oNode
	 * @param id The identifier number
	 * @param pid The parent identifier number
	 * @param state The node State
	 * 	0 - final node
	 * 	1 - directory node closed
	 * 	2 - directory node open
	 * 	3 - directory node dynamic (closed)
	 * 	4 - empty directory node
	 *	5 - root final node
	 * @param name The displayed name
	 * @param value The internal value for the node
	 * @param url The link url (href)
	 * @param icon The overloaded icon
	 */
	var oNode =  function(id, pid, state, name, value, url, icon, checked, noselection) {
		var t = this;
		t.id = id;
		t.pid = pid;
		t.state = state;
		t.name = name;
		t.value = value;
		t.url = url;
		t.checked = checked || false;
		t.noselection = noselection || 0;
		t._isLast = -1;
		t.children = [];
		t.icon = icon || null;
	};
	oNode.prototype = {
		/** Add a Child
		 */
		add : function(id) {
			this.children[this.children.length] = id;
		},
		/** Remove a Child
		 */
		rem : function(id) {
			var t=this,f=false;
			for(var i = 0; i < t.children.length; i++) {
				if( f == true )
					t.children[i-1] = t.children[i];
				else if( t.children[i] == id )
					f = true;
			}
			if( f == true )
				t.children.splice(t.children.length-1, 1);
		}
	};
	window.oNode = oNode;

	/** oTree
	 * @param id
	 * @param conf
	 * @param callbackFct
	 * @param data
	 * @param render
	 */
	var oTree = function(id, conf, callbackFct, data, render) {
		if( window.oTrees[id] )
			window.oTrees[id].destroy();

		var t = this;
		if(!conf){ conf = {}; }
		t.config = {
			rootImg: conf.rootImg || '/media/com_hikamarket/images/otree/',
			useSelection: (conf.useSelection === undefined) || conf.useSelection,
			checkbox: conf.checkbox || false,
			tricheckbox: conf.tricheckbox || false,
			showLoading: conf.showLoading || false,
			loadingText: conf.loadingText || ''
		};
		t.icon = {
			loading     : 'loading.gif',
			folder      : 'folder.gif',
			folderOpen  : 'folderopen.gif',
			node        : 'page.gif',
			line        : 'line.gif',
			join        : 'join.gif',
			joinBottom  : 'joinbottom.gif',
			plus        : 'plus.gif',
			plusBottom  : 'plusbottom.gif',
			minus       : 'minus.gif',
			minusBottom : 'minusbottom.gif',
			option      : 'option.gif'
		};
		t.lNodes = [];
		t.tmp = {
			trichecks: []
		}
		t.selectedNode = null;
		t.selectedFound = false;
		t.written = false;

		t.lNodes[0] = new oNode(0,-1);
		t.nbRemovedNodes = 0;

		t.iconWidth = 18;

		t.id = id;
		t.callbackFct = callbackFct;
		t.callbackSelection = null;
		t.callbackCheck = null;

		window.oTrees[id] = t;

		if( data ) t.load(data);
		if( render ) t.render(render);
	};
	oTree.prototype = {
		/** Destroy an oTree instance
		 */
		destroy : function() {
			var t = this;
			oTrees[t.id] = null;
			t.icon = null;
			t.config = null;
			t.lNodes = null;
			t.callbackFct = null;
			t.callbackSelection = null;
			t.loadingNode = null;
			t.nbRemovedNodes = 0;

			e = document.getElementById( t.id + '_otree' );
			if( !e )
				e = document.getElementById( t.id );
			if( e )
				e.innerHTML = '';
			t.id = null;
		},
		/** Add a new Icon in the configuration
		 * @param name
		 * @param url
		 */
		addIcon : function(name, url) {
			this.icon[name] = url;
		},
		/** Create a new Node
		 * @param pid
		 * @param state
		 * @param name
		 * @param value
		 * @param url
		 * @param icon
		 * @return id
		 */
		add : function(pid, state, name, value, url, icon, checked, noselection) {
			var t=this,id=0;
			if( !t.lNodes[pid] )
				return -1;
			if( t.nbRemovedNodes == 0 ) {
				id = t.lNodes.length;
			} else {
				for(var i = t.lNodes.length; i >= 1; i--) {
					if( t.lNodes[i] == null ) {
						id = i;
						i = 0;
						t.nbRemovedNodes--;
						break;
					}
				}
			}
			t.lNodes[id] = new oNode(id, pid, state, name, value, url, icon, checked, noselection);
			t.lNodes[pid].add(id);
			return id;
		},
		/** Load a serialized tree
		 * @param data
		 * @param pid
		 */
		load : function(data, pid) {
			if( typeof(data) != "object" ) return;
			if( typeof(pid) == "undefined" ) pid = 0;
			var nId = 0, i, l = data.length;
			for(var id = 0; id < l; id++) {
				if( typeof(data[id]) == "object" && data[id]) {
					i = data[id];
					nId = this.add(pid, i.status, i.name, i.value, i.url, i.icon, i.checked, i.noselection);
					if( i.data ) {
						this.load(i.data, nId);
					}
				}
			}
		},
		/** Create a new Node and insert it for a specific identifier
		 * @param id
		 * @param pid
		 * @param state
		 * @param name
		 * @param value
		 * @param url
		 * @param icon
		 */
		ins : function(id, pid, state, name, value, url, icon, checked, noselection) {
			if( !this.lNodes[id] ) {
				this.lNodes[id] = new oNode(id, pid, state, name, value, url, icon, checked, noselection);
				this.lNodes[pid].add(id);
			}
		},
		/** Insert a Node
		 * @param node
		 */
		insertNode : function(node) {
			this.lNodes[node.id] = node;
			this.lNodes[node.pid].add(node.id);
		},
		/** Set a Node.
		 * like "insertNode" but does not create the link with the parent.
		 * @param node
		 */
		setNode : function(node) {
			this.lNodes[node.id] = node;
		},
		/** Move a Node
		 * @param node
		 * @param dest
		 */
		moveNode : function(node,dest) {
			var t = this;
			if( typeof(node) == "number" ) node = t.get(node);
			if( typeof(dest) == "number" ) dest = t.get(dest);
			var old = t.lNodes[node.pid];
			if( old ) {
				old.rem(node.id);
				dest.add(node.id);
				node.pid = dest.id;
				t.update(old);
				t.update(dest);
			}
		},
		/** Remove a Node
		 * @param node The node to destroy (Node Object or Node Id)
		 * @param update Call an update on his parent or not
		 * @param rec Do not pass this parameter which is used for recursivity
		 */
		rem : function(node,update,rec) {
			var t=this;
			if( typeof(node) == "number" ) node = t.get(node);
			if( typeof(update) == "undefined" ) update = true;
			var p = t.get(node.pid);
			if( node && node.children.length > 0 ) {
				var o;
				for( var i = node.children.length - 1; i >= 0; i-- ) {
					o = node.children[i];
					t.rem(o, false, true);
					t.lNodes[o] = null;
				}
				node.children = [];
			}
			if( !rec ) {
				var id = node.id;
				if(p) p.rem(id);
				t.lNodes[id] = null;
			}
			t.nbRemovedNodes++;
			if( update && p )
				t.update(p);
		},
		/** Update a Node
		 * This function will call a "render"
		 * @param node The node to update (Node Object or Node Id)
		 * @return boolean
		 */
		update : function(node) {
			if( node ) {
				if( typeof(node) == "number") node = this.get(node);
				return this.render( this.id + '_d' + node.id, node.id );
			}
			return this.render();
		},
		/** Render the tree or just a part of it
		 * @param dest The render target (HTML Object or name of its ID)
		 * @param start The Node Id for the render root
		 * @return boolean
		 */
		render : function(dest, start) {
			var t = this, d = document, str = '', n;
			if( typeof(start) == "number" )
				n = t.lNodes[start];
			else
				n = t.lNodes[0];

			t.processLast();
			t.tmp.trichecks = [];

			if( t.written == true || dest ) {
				if( typeof(dest) == "boolean" || !dest ) dest = t.id;
				if( t.written == false ) {
					t.written = true;
					t.id = dest;
				}
				str = t.rnodes(n);
				var e = d.getElementById( dest + '_otree' );
				if( !e ) { e = d.getElementById( dest ); }
				if( !e ) { return false; }
				e.innerHTML = str;
			} else {
				str = '<div id="' + t.id + '_otree" class="oTree">' + t.rnodes(n) + '</div>';
				d.write(str);
				t.written = true;
			}

			if(t.config.tricheckbox && t.tmp.trichecks.length > 0) {
				var id, c;
				for(var i = t.tmp.trichecks.length - 1; i >= 0; i--) {
					id = t.tmp.trichecks[i];
					c = d.getElementById(t.id+'_c'+id);
					if(c)
						c.indeterminate = true;
				}
			}
			t.tmp.trichecks = [];

			return true;
		},
		/** Internal function
		 */
		rnodes : function(pNode) {
			var t=this,str = '';
			if(!pNode)
				return str;
			for(var i = 0; i < pNode.children.length; i++) {
				var n = pNode.children[i];
				if(t.lNodes[n])
					str += t.rnode(t.lNodes[n]);
			}
			return str;
		},
		/** Internal function
		 */
		rnode : function(node) {
			var t=this,str = '<div class="oTreeNode">', style = '', ret = '', toFind = node.pid, found = true;

			if( toFind > 0 ) {
				var white = 0;
				while(found) {
					found = false;
					if( toFind > 0 && toFind < t.lNodes.length && t.lNodes[toFind] ) {
						if(t.lNodes[toFind]._isLast == -1)
							t.lNodes[toFind]._isLast = t.isLast(t.lNodes[toFind])?1:0;
						if(t.lNodes[toFind]._isLast == 1) {
							white++;
							if( white == 6 ) {
								ret = '<div class="e'+white+'"></div>' + ret;
								white = 0;
							}
						} else {
							if( white > 0 )
								ret = '<div class="e'+white+'"></div>' + ret;
							white = 0;
							ret = '<img src="' + t.config.rootImg + t.icon.line + '" alt=""/>' + ret;
						}
						found = true;
						toFind = t.lNodes[toFind].pid;
					}
				}
				if( white > 0 )
					ret = '<div class="e'+white+'"></div>' + ret;
			}
			str += ret;

			// Cursor
			var img, last = (node._isLast == 1);
			if( node.state == 0 || node.state == 4 ) {
				img = t.icon.join;
				if( last ) img = t.icon.joinBottom;
				str += '<img src="' + t.config.rootImg + img + '" alt=""/>';
			} else if( node.state == 1 || node.state == 3 ) {
				img = t.icon.plus;
				if( last ) img = t.icon.plusBottom;
				str += '<a href="#" onclick="window.oTrees.' + t.id + '.s(' + node.id + ');return false;"><img id="'+t.id+'_j'+node.id+'" src="' + t.config.rootImg + img + '" alt=""/></a>';
			} else if( node.state == 2 ) {
				img = t.icon.minus;
				if( last ) img = t.icon.minusBottom;
				str += '<a href="#" onclick="window.oTrees.' + t.id + '.s(' + node.id + ');return false;"><img id="'+t.id+'_j'+node.id+'" src="' + t.config.rootImg + img + '" alt=""/></a>';
			}

			if(t.config.checkbox && !node.noselection) {
				var attr = '', chkName = t.config.checkbox;
				if(typeof(chkName) == "string") {
					if( chkName.substring(-1) != ']' )
						chkName += '[]';
				} else {
					chkName = t.id+'[]';
				}
				if(node.checked) {
					if(t.config.tricheckbox && node.checked === 2)
						t.tmp.trichecks[t.tmp.trichecks.length] = node.id;
					else
						attr = ' checked="checked"';
				}
				str += '<input type="checkbox" id="'+t.id+'_c'+node.id+'" onchange="window.oTrees.' + t.id + '.chk(' + node.id + ',this.checked);" name="' + chkName + '" value="' + node.value + '"' + attr + '/>';
			}

			// Icon
			str += '<img id="' + t.id + '_i' + node.id + '" alt="" src="' + t.config.rootImg;
			var name = node.name;
			if (t.config.useSelection && node.url )
				name = '<a id="'+t.id+'_s'+node.id+'" class="node" href="' + node.url + '" onclick="window.oTrees.' + t.id + '.sel(' + node.id + ');">' + node.name + '</a>';
			else if( node.url )
				name = '<a id="'+t.id+'_s'+node.id+'" class="node" href="' + node.url + '">' + node.name + '</a>';
			else if((t.config.checkbox || t.config.useSelection) && !node.noselection)
				name = '<a id="'+t.id+'_s'+node.id+'" class="node" href="#" onclick="window.oTrees.' + t.id + '.sel(' + node.id + ');return false;">' + node.name + '</a>';
			else
				name = '<span class="node">' + node.name + '</span>';

			if( node.state == 0 || node.state == 5 ) {
				if( node.icon == null )
					str += t.icon.node + '"/>' + name;
				else
					str += t.icon[node.icon] + '"/>' + name;
			} else if( node.state == 1 || node.state == 3 || node.state == 4 ) {
				if( node.icon == null )
					str += t.icon.folder + '"/>' + name;
				else
					str += t.icon[node.icon] + '"/>' + name;
				style = 'style="display:none;"';
			} else if( node.state == 2 ) {
				if( node.icon == null )
					str += t.icon.folderOpen + '"/>' + name;
				else
					str += t.icon[node.icon] + '"/>' + name;
			}
			str += '</div>';

			if( node.state > 0 ) {
				str += '<div id="' + t.id + '_d' + node.id + '" class="clip" ' + style + '>' + t.rnodes(node) + '</div>';
			}
			return str;
		},
		/** Switch Node
		 * Open or Close a Directory Node
		 * @param node The node to switch (Node Object or Node Id)
		 */
		s : function(node) {
			if( typeof(node) == "number" ) node = this.get(node);
			if( node.state == 2 )
				this.c(node);
			else
				this.o(node);
		},
		/** Open a Node
		 * @param node The node to open (Node Object or Node Id)
		 */
		o : function(node) {
			var t = this;
			if( typeof(node) == "number" ) node = t.get(node);

			// Closed Or Dynamic
			if( node && (node.state == 1 || node.state == 3) ) {
				e = document.getElementById(t.id + '_d' + node.id);
				e.style.display = '';

				// Dynamic
				if( node.state == 3 ) {
					node.children = [];
					if( t.config.showLoading ) {
						if( !t.loadingNode ) {
							t.loadingNode = new oNode(0,node.id,0,t.config.loadingText,null,null,'loading');
							t.loadingNode._isLast = 1;
						} else
							t.loadingNode.pid = node.id;

						e.innerHTML = t.rnode(t.loadingNode);
					}

					if(t.callbackFct)
						t.callbackFct(this, node, e);
				}

				if( node.icon == null ) {
					e = document.getElementById(t.id + '_i' + node.id);
					e.src = t.config.rootImg + t.icon.folderOpen;
				}

				e = document.getElementById(t.id + '_j' + node.id);
				if( t.isLast(node) )
					e.src = t.config.rootImg + t.icon.minusBottom;
				else
					e.src = t.config.rootImg + t.icon.minus;
				node.state = 2;
			}
		},
		/** Close a Node
		 * @param node The node to close (Node Object or Node Id)
		 */
		c : function(node) {
			if( typeof(node) == "number" ) node = this.get(node);

			// Open
			if( node && node.state == 2 ) {
				var t=this, d=document;
				e = d.getElementById(t.id + '_d' + node.id);
				e.style.display = 'none';

				if( node.icon == null ) {
					e = d.getElementById(t.id + '_i' + node.id);
					e.src = t.config.rootImg + t.icon.folder;
				}

				e = d.getElementById(t.id + '_j' + node.id);
				if( t.isLast(node) )
					e.src = t.config.rootImg + t.icon.plusBottom;
				else
					e.src = t.config.rootImg + t.icon.plus;
				node.state = 1;
			}
		},
		/** Open To
		 * @param node The node to open to... (Node Object or Node Id)
		 */
		oTo : function(node) {
			if( typeof(node) == "number" ) node = this.get(node);
			if( node ) {
				var t=this,toOpId = node.pid;
				while( toOpId > 0 && toOpId < t.lNodes.length ) {
					this.o(t.lNodes[toOpId]);
					toOpId = t.lNodes[toOpId].pid;
				}
			}
		},
		/** Make a Selection
		 * @param id The Node Id to select (could be a node object)
		 */
		sel : function(id) {
			if(id === null) return;
			if( typeof(id) != "number" ) id = id.id;

			var t=this,d=document,cn = t.lNodes[id];
			if(!cn) return;
			if(!t.config.useSelection && !t.config.checkbox) return;
			if(t.config.checkbox) {
				t.chk(cn,-1);
			}
			if(!t.config.useSelection) return;
			if( t.selectedNode != id ) {
				var e;
				if (t.selectedNode || t.selectedNode == 0) {
					e = d.getElementById(t.id + '_s' + t.selectedNode);
					if( e )
						e.className = "node";
				}
				e = d.getElementById(t.id + '_s' + id);
				if(e)
					e.className = "nodeSel";
				t.selectedNode = id;
				if( t.callbackSelection )
					t.callbackSelection(this, t.selectedNode);
			} else {
				var e = d.getElementById(t.id + '_s' + id);
				if(e)
					e.className = "nodeSel";
			}
		},
		/**
		 *
		 */
		chk : function(id, value, call, fromP) {
			if(id === null) return;
			if(typeof(id) == "object") id = id.id;
			if(!this.config.checkbox) return;

			var t=this,d=document,cn=t.lNodes[id];
			if(!cn) return;
			var oldState = cn.checked;
			if(typeof(value) == "number" && value < 0) {
				if(cn.checked == 2)
					cn.checked = true;
				else
					cn.checked = !cn.checked;
			} else
				cn.checked = value;
			var e = d.getElementById(t.id+'_c'+id);
			if(e) {
				e.checked = cn.checked;
				if(!t.config.tricheckbox)
					e.indeterminate = false;
				if(t.config.tricheckbox && oldState != cn.checked) {
					e.indeterminate = false;
					if(value === 2) {
						e.checked = false;
						e.indeterminate = true;
						cn.checked = 2;
					} else {
						// Check/uncheck all children
						for(var i = cn.children.length - 1; i >= 0; i--) {
							t.chk(cn.children[i], cn.checked, call, true);
						}
					}
					if(fromP === undefined) {
						// Check/uncheck parent if necessary
						var p = t.lNodes[cn.pid], o = null, cpt = 0;
						if(p) {
							for(var i = p.children.length - 1; i >= 0; i--) {
								o = t.lNodes[p.children[i]];
								if(o && o.checked && o.checked === true) {
									cpt++;
								}
							}
							if(cpt == p.children.length || cpt == 0) {
								t.chk(p, cn.checked, call);
							} else {
								t.chk(p, 2, call)
							}
						}
					}
				}
			}
			if((call === undefined || call === null || call) && t.callbackCheck)
				t.callbackCheck(this, id, value);
		},
		/**
		 *
		 */
		chks : function(ids,call,useId) {
			var t = this;
			if(!t.config.checkbox) return;
			if(useId === undefined) useId = true;
			if(call === undefined) call = false;
			if( typeof(ids) == "string") {
				// Check all
				if(ids == "*") {
					for(var i = 0; i < t.lNodes.length; i++) {
						if(t.lNodes[i] && !t.lNodes[i].checked)
							t.chk(t.lNodes[i],true,call);
					}
					return;
				}
				ids = ids.split(",");
			}
			for(var i = 0; i < t.lNodes.length; i++) {
				if(t.lNodes[i] && t.lNodes[i].checked)
					t.chk(t.lNodes[i],false,call);
			}
			if(useId) {
				for(var j = ids.length -1; j >= 0; j--) {
					var v = parseInt(ids[j]);
					t.chk(v,true,call);
				}
			} else {
				for(var j = ids.length -1; j >= 0; j--) {
					for(var i = 0; i < t.lNodes.length; i++) {
						if( t.lNodes[i] && t.lNodes[i].value == ids[j] ) {
							t.chk(i,true,call);
							break;
						}
					}
				}
			}
		},
		/**
		 *
		 */
		getChk : function() {
			var t = this, ret = [];
			if(!t.config.checkbox) return false;
			for(var i = 0; i < t.lNodes.length; i++) {
				if(t.lNodes[i] && t.lNodes[i].checked && t.lNodes[i].checked === true && t.lNodes[i].value)
					ret.push(t.lNodes[i].value);
			}
			return ret;
		},
		/** Find a Node
		 * @param value The value to found
		 * @param mode The mode for node state
		 *	[null] - all nodes
		 *	0 - Final nodes
		 *	1 - Directory nodes
		 * @return the first node object which matched
		 */
		find : function(value, mode) {
			if( typeof(mode) == "undefined" ) mode = -1;

			var t = this;
			for(var i = 0; i < t.lNodes.length; i++) {
				if( t.lNodes[i] && t.lNodes[i].value == value ) {
					if( mode == -1 )
						return t.lNodes[i];
					if( mode == 0 && (t.lNodes[i].state == 0 || t.lNodes[i].state == 5) )
						return t.lNodes[i];
					if( mode == 1 && t.lNodes[i].state >= 1 && t.lNodes[i].state != 5 )
						return t.lNodes[i];
				}
			}
			return null;
		},
		/** Empty a directory
		 * @param node The node to empty (Node Object or Node Id)
		 */
		emptyDirectory : function(node) {
			if( typeof(node) == "number" ) node = this.get(node);
			if( node.state == 1 || node.state == 2 || node.state == 3 ) {
				var t = this, d = document;
				var e = d.getElementById(t.id + '_j' + node.id);
				var a = e.parentNode;

				var src = t.config.rootImg + t.icon.join;
				if( node._isLast == 1 )
					src = t.config.rootImg + t.icon.joinBottom;

				a.parentNode.replaceChild(e, a);
				e.src = src;
				node.state = 4;

				if( node.icon == null ) {
					e = d.getElementById(t.id + '_i' + node.id);
					if(!e) return;
					e.src = t.config.rootImg + t.icon.folder;
				}

				e = d.getElementById(t.id + '_d' + node.id);
				if(!e) return;
				e.style.display = 'none';
				e.innerHTML = '';

				if( node && node.children.length > 0 ) {
					var o;
					for( var i = node.children.length - 1; i >= 0; i-- ) {
						o = node.children[i];
						t.rem(o, false);
						t.lNodes[o] = null;
					}
				}
				node.children = [];
			}
		},
		/** Get a node
		 * @param id The node id
		 * @return the node object
		 */
		get : function(id) {
			if( id >= 0 && id < this.lNodes.length && this.lNodes[id] ) {
				try {
					return this.lNodes[id];
				} catch(e) {
					return null;
				}
			}
			return null;
		},
		/** Internal function
		 */
		isLast : function(node) {
			try {
				var pChildren = this.lNodes[node.pid].children;
				return ( pChildren[pChildren.length - 1] == node.id );
			} catch(e) {}
			return true;
		},
		/** Internal function
		 * currently unused. Deprecated?
		 */
		cleanLast : function() {
			for( var i = this.lNodes.length - 1; i >= 0; i-- )
				this.lNodes[i]._isLast = -1;
		},
		/** Internal function
		 */
		processLast : function() {
			var t=this,n;
			for( var i = t.lNodes.length - 1; i >= 0; i-- ) {
				if( t.lNodes[i] && t.lNodes[i].children.length > 0 ) {
					n = t.lNodes[i].children[ t.lNodes[i].children.length - 1 ];
					t.lNodes[n]._isLast = 1;
					for( var j = t.lNodes[i].children.length - 2; j >= 0; j-- ) {
						t.lNodes[ t.lNodes[i].children[ j ]]._isLast = 0;
					}
				}
			}
		},
		/**
		 */
		deep : function(node, max) {
			if( typeof(node) == "number" ) node = this.get(node);
			if( node == null ) return -1;
			if( typeof(max) == "undefined" ) max = 100;
			var ret = 0, toFind = node.pid;
			if( toFind == -1 )
				return ret;
			while(toFind > 0 && this.lNodes[toFind]) {
				ret++;
				toFind = this.lNodes[toFind].pid;
				if( ret >= max )
					return ret;
			}
			return ret;
		},
		/**
		 */
		search: function(text) {
			var t=this,d=document,r=null,e=null,pid=0;

			if(text) {
				r = new RegExp(text,"i");
				for(var i = 0; i < t.lNodes.length; i++) {
					if( t.lNodes[i] ) {
						t.lNodes[i].search = -1;
					}
				}
				for(var i = 0; i < t.lNodes.length; i++) {
					if( t.lNodes[i] ) {
						if(r.test(t.lNodes[i].name)) {
							if(t.lNodes[i].search <= 0) {
								t.lNodes[i].search = 2;
								pid = t.lNodes[i].pid;
								while(pid > 0 && t.lNodes[pid] && t.lNodes[pid].search <= 0) {
									t.lNodes[pid].search = 1;
									pid = t.lNodes[pid].pid;
								}
							} else {
								t.lNodes[i].search = 2;
							}
						} else {
							if(t.lNodes[i].search < 0)
								t.lNodes[i].search = 0;
						}
					}
				}

			}
			for(var i = 0; i < t.lNodes.length; i++) {
				if(t.lNodes[i]) {
					e = d.getElementById(t.id + '_s' + t.lNodes[i].id);
					if(!text) {
						t.lNodes[i].search = null;
						if(e) {
							e.parentNode.style.display = '';
							e.className = "node";
						}
					} else {
						if(e) {
							e.className = "node";
							if(t.lNodes[i].search > 0) {
								e.parentNode.style.display = '';
								if(t.lNodes[i].search > 1)
									e.className = "nodeSel";
							} else
								e.parentNode.style.display = 'none';
						}
					}
				}
			}
		}
	};
	window.oTree = oTree;
})();
