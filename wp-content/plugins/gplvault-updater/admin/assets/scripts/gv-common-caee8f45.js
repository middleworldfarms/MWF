function VaultUpdateError(message, title, data, fileName, lineNumber) {
	var instance = new Error(message, fileName, lineNumber);
	instance.name = 'UpdateError';
	instance.title = title || '';
	data = data || {};
	instance.data = data;

	Object.setPrototypeOf(instance, Object.getPrototypeOf(this));
	if (Error.captureStackTrace) {
		Error.captureStackTrace(instance, VaultUpdateError);
	}

	return instance;
}
VaultUpdateError.prototype = Object.create(Error.prototype, {
	constructor: {
		value: Error,
		enumerable: false,
		writable: true,
		configurable: true,
	},
});

VaultUpdateError.prototype.toString = function () {
	return this.message;
};

if (Object.setPrototypeOf) {
	Object.setPrototypeOf(VaultUpdateError, Error);
} else {
	VaultUpdateError.__proto__ = Error;
}

function ValidationError(message, title, fileName, lineNumber) {
	var instance = new Error(message, fileName, lineNumber);
	instance.name = 'ValidationError';
	instance.title = title || '';

	Object.setPrototypeOf(instance, Object.getPrototypeOf(this));
	if (Error.captureStackTrace) {
		Error.captureStackTrace(instance, ValidationError);
	}
	return instance;
}

ValidationError.prototype = Object.create(Error.prototype, {
	constructor: {
		value: Error,
		enumerable: false,
		writable: true,
		configurable: true,
	},
});

ValidationError.prototype.toString = function () {
	return this.message;
};

if (Object.setPrototypeOf) {
	Object.setPrototypeOf(ValidationError, Error);
} else {
	ValidationError.__proto__ = Error;
}

(function ($, undefined) {
	'$:nomunge'; // Used by YUI compressor.

	$.fn.serializeObj = function () {
		var obj = {};

		$.each(this.serializeArray(), function (i, o) {
			var n = o.name,
				v = o.value;

			obj[n] =
				obj[n] === undefined
					? v
					: Array.isArray(obj[n])
						? obj[n].concat(v)
						: [obj[n], v];
		});

		return remapObj(obj);
	};

	function remapObj(o) {
		var build, key, newKey, value;

		if (o === null || typeof o !== 'object') {
			return o;
		}

		if (_.isArray(o)) {
			return o.map(remapObj);
		}

		build = {};
		for (key in o) {
			newKey = key.replace('[]', '');
			value = o[key];

			if (typeof value === 'object') {
				value = remapObj(value);
			}

			build[newKey] = value;
		}

		return build;
	}
})(jQuery);

window.gplvault = window.gplvault || {};
(function ($, gplvault, settings, wp) {
	var $doc = $(document),
		__ = wp.i18n.__,
		_x = wp.i18n._x,
		gv = gplvault || {},
		console = window.console,
		sprintf = wp.i18n.sprintf;

	gv.template = function (id) {
		var _id = 'gv-templates-' + id;

		return wp.template(_id);
	};
	gv.common = {};
	gv.common.settings = settings || {};
	if (settings.pagenow.length > 0) {
		window.pagenow = settings.pagenow;
	}

	gv.common.isPromise = function (value) {
		return typeof value === 'object' && typeof value.then === 'function';
	};

	gv.common.tippy = tippy(document.querySelectorAll('.gv-has-tooltip'));

	gv.common.togglePW = function (e) {
		var toggler = $(e.currentTarget),
			parentEl = toggler.closest('.gv-fields__field'),
			pwField = parentEl.find('input');

		if ('password' === pwField.attr('type')) {
			pwField.attr('type', 'text');
			resetToggle(false);
		} else {
			pwField.attr('type', 'password');
			resetToggle(true);
		}

		function resetToggle(show) {
			toggler
				.attr({
					'aria-label': show
						? __('Show password', 'gplvault')
						: __('Hide password', 'gplvault'),
				})
				.find('.text')
				.text(show ? __('Show', 'gplvault') : __('Hide', 'gplvault'))
				.end()
				.find('.dashicons')
				.removeClass(show ? 'dashicons-hidden' : 'dashicons-visibility')
				.addClass(show ? 'dashicons-visibility' : 'dashicons-hidden');
		}
	};

	gv.common.popup = function (args, selector) {
		selector = selector || 'gv_popups';
		args = args || {};
		args = _.extend(settings.popup, args);

		function _initialize() {
			return new Polipop(selector, args);
		}

		var _instance = _initialize();

		return _instance;
	};

	gv.common.notifier = gv.common.popup();
	gv.common.setPopupOptions = function (options) {
		options = options || {};
		_.each(options, function (option, k) {
			gv.common.notifier.setOption(k, option);
		});

		return gv.common.notifier;
	};

	gv.common.resetPopup = function () {
		gv.common.notifier = gv.common.popup();
		return gv.common.notifier;
	};

	gv.common.ajax = function (options) {
		var defaults = {
			action: settings.ajax_action,
			security: settings.ajax_nonce,
		};
		return wp.ajax.post(_.extend(defaults, options));
	};

	var $report_box = $doc.find('#gv_notice');
	$report_box.on('click', 'button.notice-dismiss', function (e) {
		e.preventDefault();
		var $instance = $(this);

		$instance.closest('.is-dismissible').remove();
	});

	$('.gv-select2').select2();

	function windowScrollHandler() {
		var scroll = $(window).scrollTop();
		var $header = $doc.find('.gv-layout__header');

		if ($header.length < 1) {
			return;
		}
		if (scroll >= 50) {
			$header.addClass('is-scrolled');
		} else {
			$header.removeClass('is-scrolled');
		}
	}

	// var throttledScrollHandler = _.throttle(windowScrollHandler, 100);
	$(window).on('scroll', windowScrollHandler);
})(jQuery, window.gplvault, window._gvCommonSettings, window.wp);
