/**
 * This file is part of MolView (http://molview.org)
 * Copyright (c) 2014, Herman Bergwerf
 *
 * MolView is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * MolView is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with MolView.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * Create new MPAtom
 * @param {MolPad} mp
 * @param {Object} obj Configuration
 */
function MPAtom(mp, obj)
{
	this.mp = mp;
	this.index = obj.i;
	this.center = new MPPoint(obj.x || 0, obj.y || 0);
	this.element = obj.element || "C";
	this.charge = obj.charge || 0;
	this.isotope = obj.isotope || 0;
	this.bonds = obj.bonds !== undefined ? obj.bonds.slice() : [];//deep copy
	this.selected = false;
	this.display = "normal";
	this.valid = false;
	this.mp.invalidate();
}

MPAtom.prototype.getX = function() { return this.center.x; }
MPAtom.prototype.getY = function() { return this.center.y; }

/**
 * Retruns data which can be used as input by the Ketcher fork
 * @param {Object} mp
 * @return {Object}
 */
MPAtom.prototype.getKetcherData = function(mp)
{
	return new chem.Struct.Atom({
		pp: {
			x: this.center.x / mp.settings.bond.length,
			y: this.center.y / mp.settings.bond.length
		},
		label: this.element,
		charge: this.charge,
		isotope: this.isotope
	});
}

/**
 * Retruns config data which can be used to reconstruct this object
 * @return {Object}
 */
MPAtom.prototype.getConfig = function()
{
	return {
		i: this.index,
		x: this.center.x,
		y: this.center.y,
		element: this.element,
		charge: this.charge,
		isotope: this.isotope,
		bonds: this.bonds.slice()
	};
}

/**
 * Retruns MPAtom string which can be compared to other MPAtom strings
 * @return {String}
 */
MPAtom.prototype.toString = function()
{
	var str = this.center.x.toString()
		+ this.center.y.toString()
		+ this.element.toString()
		+ this.charge.toString()
		+ this.isotope.toString();

	for(var i = 0; i < this.bonds.length; i++)
	{
		str += this.mp.molecule.bonds[this.bonds[i]].toString();
	}

	return str;
}

/**
 * Generates charge label for this atom
 * @return {String} Charge label as string
 */
MPAtom.prototype.getChargeLabel = function()
{
	return this.charge == 0 ? "" :
		this.charge == -1 ? "\u2212" :
		this.charge ==  1 ? "+" :
			(this.charge > 1 ? "+" : "-") + Math.abs(this.charge);
}

MPAtom.prototype.setIndex = function(index) { this.index = index; }

MPAtom.prototype.setCenter = function(x, y)
{
	this.center.replace(x, y);
	this.invalidate(true);
}

MPAtom.prototype.setElement = function(element)
{
	this.element = element == "D" ? "H" : element;
	this.invalidate(false);
}

MPAtom.prototype.setCharge = function(charge)
{
	this.charge = charge;
	this.invalidate(false);
}

MPAtom.prototype.setIsotope = function(isotope)
{
	this.isotope = isotope;
	this.invalidate(false);
}

/**
 * Sets display type
 * @param {String} type
 */
MPAtom.prototype.setDisplay = function(type)
{
	if(type != this.display)
	{
		this.display = type;
		this.invalidate(false);
	}
}

/**
 * Checks if this MPAtom is equal to another MPAtom
 * @param  {MPAtom} atom
 * @return {Booelan}
 */
MPAtom.prototype.equals = function(atom)
{
	return this.index == atom.index;
}

/**
 * Finds this MPAtom if it is an implicit hydrogen atom
 * All H atoms bonded to a C atom without stereo information are considered implicit
 * @return {Boolean}    Indicates if this atom is implicit
 */
MPAtom.prototype.isImplicit = function()
{
	if(this.element == "H" && this.isotope == 0 &&
			this.charge == 0 && this.bonds.length == 1)
	{
		var bond = this.mp.molecule.bonds[this.bonds[0]];
		if(bond.type == MP_BOND_SINGLE && bond.stereo == MP_STEREO_NONE &&
			bond.isPair("C", "H"))
		{
			return true;
		}
	}
	return false;
}

/**
 * Checks if the given index is a neighbor atom and return connecting bond
 * @param  {Integer} idx
 * @return {Integer} Bond index or -1
 */
MPAtom.prototype.getNeighborBond = function(idx)
{
	for(var i = 0; i < this.bonds.length; i++)
	{
		if(this.mp.molecule.bonds[this.bonds[i]].getOppositeAtom(this.index) == idx)
		{
			return this.bonds[i];
		}
	}
	return -1;
}

/**
 * Returns if this atom has any neighbor atoms which are not selected
 */
MPAtom.prototype.hasUnselectedNeighbors = function()
{
	for(var i = 0; i < this.bonds.length; i++)
	{
		if(!this.mp.molecule.atoms[this.mp.molecule.bonds[this.bonds[i]]
			.getOppositeAtom(this.index)].selected)
		{
			return true;
		}
	}
	return false;
}

/**
 * Checks if this atom is visible in the drawing based on MolPad display settings
 */
MPAtom.prototype.isVisible = function()
{
	if(this.display == "hidden")
	{
		return false;
	}
	else if(this.mp.settings.skeletonDisplay)
	{
		if(this.element == "C" && this.charge == 0 && this.isotope == 0)
		{
			var singleBonds = 0;
			if(this.bonds.length == 0) return true;
			else if(this.bonds.length == 2 &&
				this.mp.molecule.bonds[this.bonds[0]].type ==
				this.mp.molecule.bonds[this.bonds[1]].type &&
				this.mp.molecule.bonds[this.bonds[0]].type == MP_BOND_DOUBLE)
			{
				return true;
			}
			else return false;
		}
		return true;
	}
	else return true;
}

/**
 * Selects or deselects this MPAtom
 * @param  {Boolean} select
 */
MPAtom.prototype.select = function(select)
{
	if(!(this.selected == select &&
			//make sure this.index is not in the selection while
			(this.mp.tool.selection.indexOf(this.index) != -1) == select))
	{
		this.selected = select;
		var idx = this.mp.tool.selection.indexOf(this.index);
		if(idx == -1)
		{
			if(select)
			{
				this.mp.tool.selection.push(this.index);
			}
		}
		else if(!select)
		{
			this.mp.tool.selection.splice(idx, 1);
		}
		this.mp.invalidate();
	}
}

/**
 * MPPoint.translate wrapper of atom center point
 */
MPAtom.prototype.translate = function(x, y)
{
	this.center.translate(x, y);
	this.invalidate(true);
}

/**
 * MPPoint.rotateAroundCenter wrapper of atom center point
 */
MPAtom.prototype.rotateAroundCenter = function(c, a)
{
	this.center.rotateAroundCenter(c, a);
	this.invalidate(true);
}

/**
 * Returns total bond count
 */
MPAtom.prototype.getBondCount = function()
{
	var ret = 0;
	for(var i = 0; i < this.bonds.length; i++)
	{
		ret += this.mp.molecule.bonds[this.bonds[i]].type;
	}
	return ret;
}

/**
 * Add bond index to this atom
 * @param {Integer} bond Bond index
 */
MPAtom.prototype.addBond = function(bond)
{
	this.bonds.push(bond);
	this.invalidate(false);
}

/**
 * Map bond indices using an index map
 * The map should contain the new indexes as follows: map[old] = new
 * If map[old] === undefined, the old index is removed from the bonds list
 *
 * @param {Array}   map
 */
MPAtom.prototype.mapBonds = function(map)
{
	this.bonds = mapArray(this.bonds, map);

	/* CAUTION: this.invalidate should not be triggerd since it might
	hurt the mapping process */
}

/**
 * Replace a given bond index with another bond index
 * @param {Integer} o Old bond index
 * @param {Integer} n New bond index
 */
MPAtom.prototype.replaceBond = function(o, n)
{
	var idx = this.bonds.indexOf(o);
	var nidx = this.bonds.indexOf(n);

	if(idx != -1)
	{
		if(nidx != -1) this.bonds.splice(o, 1);
		else this.bonds[idx] = n;
	}

	this.invalidate(false);
}

/**
 * Adds new bond to this atom using the given element and angle
 * Does not redraw the canvas
 * @param  {Object} config { element, a, type, stereo }
 * @return {Object}        Tool data for this bond
 */
MPAtom.prototype.addNewBond = function(config)
{
	var atom = new MPAtom(this.mp, {
		i: this.mp.molecule.atoms.length,
		x: this.getX() + (config.length || this.mp.settings.bond.length) * Math.cos(config.a),
		y: this.getY() - (config.length || this.mp.settings.bond.length) * Math.sin(config.a),//y axis is flipped
		element: config.element || "C"
	});

	var bond = new MPBond(this.mp, {
		i: this.mp.molecule.bonds.length,
		type: config.type || MP_BOND_SINGLE,
		stereo: config.stereo || MP_STEREO_NONE,
		from: this.index,
		to: atom.index
	});

	atom.addBond(bond.index);
	this.addBond(bond.index);

	this.mp.molecule.atoms.push(atom);
	this.mp.molecule.bonds.push(bond);

	return {
		atom: atom.index,
		bond: bond.index,
		startAngle: config.a,
		currentAngle: config.a
	};
}

/**
 * Saturate atom with hydrogens
 * C atoms are saturated using their four binding sites
 */
MPAtom.prototype.addImplicitHydrogen = function()
{
	if(this.element == "C")
	{
		if(this.getBondCount() == 2 && this.bonds.length == 2)
		{
			var af = this.mp.molecule.bonds[this.bonds[0]].getAngle(this);
			var at = this.mp.molecule.bonds[this.bonds[1]].getAngle(this);
			var da = Math.max(af, at) - Math.min(af, at);

			//do only display 2 Hydrogens on one side if the bonds are not parallel
			if(da < Math.PI - this.mp.settings.bond.straightDev ||
				da > Math.PI + this.mp.settings.bond.straightDev)
			{
				var a = this.calculateNewBondAngle(2);
				if(a == 0) return;

				this.addNewBond({
					a: a[0],
					length: this.mp.settings.bond.lengthHydrogen,
					element: "H"
				});
				this.addNewBond({
					a: a[1],
					length: this.mp.settings.bond.lengthHydrogen,
					element: "H"
				});

				this.invalidate(false);
				return;
			}
		}

		while(this.getBondCount() < 4)
		{
			var a = this.calculateNewBondAngle();
			this.addNewBond({
				a: a,
				length: this.mp.settings.bond.lengthHydrogen,
				element: "H"
			});
		}
	}
}

/**
 * Invalidate render data of this atom and neighbor bonds.
 * In most cases, only the label size has changed. Since bond vertices are based
 * on the from/to atom center, 2nd level neighbor bonds do not change
 * If the center position is updated, the 2nd level neighbor bonds should also
 * be updated if skeleton display is enabled since 1st level bonds might be used
 * to fit the 2nd level bond more precisely (if !atom.isVisible())
 *
 * @param {Boolean} newCenter Indicates if the center position is updated
 */
MPAtom.prototype.invalidate = function(newCenter)
{
	this.valid = false;

	for(var i = 0; i < this.bonds.length; i++)
	{
		/* in some cases, addBond is called while the bond
		has not been created yet like in MolPad.loadMol */
		if(this.mp.molecule.bonds[this.bonds[i]] === undefined) continue;
		this.mp.molecule.bonds[this.bonds[i]].invalidateFrom(this.index, newCenter);
	}

	this.mp.invalidate();
}

/**
 * Invalidates all connected bonds and itself
 */
MPAtom.prototype.invalidateBonds =  function()
{
	this.valid = false;

	for(var i = 0; i < this.bonds.length; i++)
	{
		this.mp.molecule.bonds[this.bonds[i]].invalidateFrom(this.index, false);
	}

	this.mp.invalidate();
}

/**
 * Validates this MPAtom by updating all its drawing data
 */
MPAtom.prototype.validate = function()
{
	if(this.valid) return;
	this.valid = true;

	this.line = this.calculateCenterLine();
}

/**
 * Render methods
 */

/**
 * Draw additional MPAtom background based on MPAtom.display
 */
MPAtom.prototype.drawStateColor = function()
{
	this.validate();

	if(this.display == "hover" || this.display == "active" ||
			(this.display == "normal" && this.selected))
	{
		var d = this.selected ? "selected" : this.display;

		this.mp.ctx.beginPath();
		if(this.line.area.point)
		{
			this.mp.ctx.arc(this.line.area.point.x, this.line.area.point.y,
					this.mp.settings.atom.radiusScaled, 0, PI2);
			this.mp.ctx.fillStyle = this.mp.settings.atom[d].color;
			this.mp.ctx.fill();
		}
		else
		{
			this.mp.ctx.moveTo(this.line.area.left.x, this.line.area.left.y);
			this.mp.ctx.lineTo(this.line.area.right.x, this.line.area.right.y);
			this.mp.ctx.strokeStyle = this.mp.settings.atom[d].color;
			this.mp.ctx.stroke();
		}
	}
}

/**
 * Draw actual atom label
 */
MPAtom.prototype.drawLabel = function()
{
	//TODO: add support for collapsed groups (CH2- to H2C-, OH- to HO-, etc.)

	this.validate();

	if(this.isVisible())
	{
		if(this.mp.settings.atom.colored)
		{
			this.mp.ctx.fillStyle = JmolAtomColorsHashHex[this.element];
		}

		if(this.mp.settings.atom.miniLabel)
		{
			var s = this.mp.settings.atom.miniLabelSize;
			this.mp.ctx.fillRect(this.center.x - s / 2, this.center.y - s / 2, s, s);
		}
		else
		{
			var x = this.center.x + this.line.text.offsetLeft;

			if(this.isotope > 0)
			{
				this.mp.setFont("isotope");
				this.mp.ctx.fillText("" + this.isotope, x, this.center.y +
						this.line.text.offsetTop - this.line.text.isotopeHeight);
				x += this.line.text.isotopeWidth;
			}

			this.mp.setFont("element");
			this.mp.ctx.fillText("" + this.element, x, this.center.y + this.line.text.offsetTop);
			x += this.line.text.labelWidth;

			if(this.charge != 0)
			{
				this.mp.setFont("charge");
				this.mp.ctx.fillText(this.getChargeLabel(), x, this.center.y +
						this.line.text.offsetTop - this.line.text.chargeHeight);
			}
		}
	}
}
