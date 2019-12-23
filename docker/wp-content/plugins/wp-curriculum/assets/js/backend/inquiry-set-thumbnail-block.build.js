/******/ (function(modules) { // webpackBootstrap
/******/ 	// The module cache
/******/ 	var installedModules = {};
/******/
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/
/******/ 		// Check if module is in cache
/******/ 		if(installedModules[moduleId]) {
/******/ 			return installedModules[moduleId].exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = installedModules[moduleId] = {
/******/ 			i: moduleId,
/******/ 			l: false,
/******/ 			exports: {}
/******/ 		};
/******/
/******/ 		// Execute the module function
/******/ 		modules[moduleId].call(module.exports, module, module.exports, __webpack_require__);
/******/
/******/ 		// Flag the module as loaded
/******/ 		module.l = true;
/******/
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/
/******/
/******/ 	// expose the modules object (__webpack_modules__)
/******/ 	__webpack_require__.m = modules;
/******/
/******/ 	// expose the module cache
/******/ 	__webpack_require__.c = installedModules;
/******/
/******/ 	// define getter function for harmony exports
/******/ 	__webpack_require__.d = function(exports, name, getter) {
/******/ 		if(!__webpack_require__.o(exports, name)) {
/******/ 			Object.defineProperty(exports, name, {
/******/ 				configurable: false,
/******/ 				enumerable: true,
/******/ 				get: getter
/******/ 			});
/******/ 		}
/******/ 	};
/******/
/******/ 	// getDefaultExport function for compatibility with non-harmony modules
/******/ 	__webpack_require__.n = function(module) {
/******/ 		var getter = module && module.__esModule ?
/******/ 			function getDefault() { return module['default']; } :
/******/ 			function getModuleExports() { return module; };
/******/ 		__webpack_require__.d(getter, 'a', getter);
/******/ 		return getter;
/******/ 	};
/******/
/******/ 	// Object.prototype.hasOwnProperty.call
/******/ 	__webpack_require__.o = function(object, property) { return Object.prototype.hasOwnProperty.call(object, property); };
/******/
/******/ 	// __webpack_public_path__
/******/ 	__webpack_require__.p = "";
/******/
/******/ 	// Load entry module and return exports
/******/ 	return __webpack_require__(__webpack_require__.s = 0);
/******/ })
/************************************************************************/
/******/ ([
/* 0 */
/***/ (function(module, exports) {

var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _possibleConstructorReturn(self, call) { if (!self) { throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); } return call && (typeof call === "object" || typeof call === "function") ? call : self; }

function _inherits(subClass, superClass) { if (typeof superClass !== "function" && superClass !== null) { throw new TypeError("Super expression must either be null or a function, not " + typeof superClass); } subClass.prototype = Object.create(superClass && superClass.prototype, { constructor: { value: subClass, enumerable: false, writable: true, configurable: true } }); if (superClass) Object.setPrototypeOf ? Object.setPrototypeOf(subClass, superClass) : subClass.__proto__ = superClass; }

var __ = wp.i18n.__;
var registerBlockType = wp.blocks.registerBlockType;
var InspectorControls = wp.editor.InspectorControls;
var SelectControl = wp.components.SelectControl;
var Component = wp.element.Component;
var elem = wp.element.createElement;

var myInquirySets = function (_Component) {
    _inherits(myInquirySets, _Component);

    _createClass(myInquirySets, null, [{
        key: 'getInitialState',
        value: function getInitialState(selectedInquirySet) {
            return {
                posts: [],
                selectedInquirySet: selectedInquirySet,
                post: {}
            };
        }
    }]);

    function myInquirySets() {
        _classCallCheck(this, myInquirySets);

        var _this = _possibleConstructorReturn(this, (myInquirySets.__proto__ || Object.getPrototypeOf(myInquirySets)).apply(this, arguments));

        _this.state = _this.constructor.getInitialState(_this.props.attributes.selectedInquirySet);

        _this.getOptions = _this.getOptions.bind(_this);

        _this.getOptions();

        _this.onChangeSelectInquirySet = _this.onChangeSelectInquirySet.bind(_this);

        return _this;
    }

    _createClass(myInquirySets, [{
        key: 'onChangeSelectInquirySet',
        value: function onChangeSelectInquirySet(value) {
            var post = this.state.posts.find(function (item) {
                return item.id == parseInt(value);
            });
            var image_url = wp_nn_theme.theme_path + '/images/default-image.png';

            this.setState({ selectedInquirySet: parseInt(value), post: post });

            if (post.featured_image_url) {
                image_url = post.featured_image_url;
            }

            this.props.setAttributes({
                selectedInquirySet: parseInt(value),
                title: post.title.rendered,
                link: post.link,
                grade: post.oer_lp_grades,
                featuredImage: image_url
            });
        }
    }, {
        key: 'getOptions',
        value: function getOptions() {
            var _this2 = this;

            var inquirysets = new wp.api.collections.Inquiryset();

            return inquirysets.fetch().then(function (posts) {
                if (posts && 0 !== _this2.state.selectedInquirySet) {
                    var post = posts.find(function (item) {
                        return item.id == _this2.state.selectedInquirySet;
                    });
                    _this2.setState({ post: post, posts: posts });
                } else {
                    _this2.setState({ posts: posts });
                }
            });
        }
    }, {
        key: 'render',
        value: function render() {

            var options = [{ value: 0, label: __('Select an inquiry set') }];
            var output = __('Loading Inquiry Sets');

            if (this.state.posts.length > 0) {
                var loading = __('We have %d inquiry sets. Choose one.');
                output = loading.replace('%d', this.state.posts.length);
                this.state.posts.forEach(function (post) {
                    options.push({ value: post.id, label: post.title.rendered });
                });
            } else {
                output = __('No inquiry set found. Please create some first.');
            }

            return [!!this.props.isSelected && wp.element.createElement(
                InspectorControls,
                { key: 'inspectorset' },
                wp.element.createElement(SelectControl, { onChange: this.onChangeSelectInquirySet, value: this.props.attributes.selectedInquirySet, label: __('Inquiry Set:'), options: options })
            ), wp.element.createElement(
                'a',
                { href: this.props.attributes.link, className: 'lp-tc-related-inquiry-block-link', rel: 'noopener noreferrer' },
                wp.element.createElement(
                    'div',
                    { className: 'lp-tc-related-inquiry-blocks-padding' },
                    wp.element.createElement(
                        'div',
                        { className: 'media-image' },
                        wp.element.createElement(
                            'div',
                            { className: 'image-thumbnail' },
                            wp.element.createElement(
                                'div',
                                { className: 'image-section' },
                                wp.element.createElement('img', { src: this.props.attributes.featuredImage, alt: '', className: 'img-thumbnail-square img-responsive img-loaded' })
                            )
                        )
                    ),
                    wp.element.createElement(
                        'div',
                        { className: 'lp-tc-related-inquiry-grades' },
                        wp.element.createElement(
                            'span',
                            null,
                            this.props.attributes.grade
                        )
                    ),
                    wp.element.createElement('div', { className: 'custom-bg-dark custom-bg-dark-inquiry-sets' }),
                    wp.element.createElement(
                        'div',
                        { className: 'lp-tc-related-inquiry-set-description' },
                        wp.element.createElement(
                            'h4',
                            null,
                            this.props.attributes.title
                        )
                    )
                )
            )];
        }
    }]);

    return myInquirySets;
}(Component);

wp.blocks.registerBlockType('wp-curriculum/inquiry-set-thumbnail-block', {
    title: 'Inquiry Set Thumbnail Block',
    category: 'common',
    icon: 'welcome-learn-more',
    attributes: {
        selectedInquirySet: { type: 'number', default: 0 },
        title: { type: 'string' },
        link: { type: 'string' },
        grade: { type: 'string' },
        featuredImage: { type: 'string', default: wp_nn_theme.theme_path + '/images/default-image.png' }
    },

    edit: myInquirySets,

    save: function save(props) {
        var className = props.className,
            attributes = props.attributes;


        return wp.element.createElement(
            'a',
            { href: attributes.link, className: 'lp-tc-related-inquiry-block-link', rel: 'noopener noreferrer' },
            wp.element.createElement(
                'div',
                { className: 'lp-tc-related-inquiry-blocks-padding' },
                wp.element.createElement(
                    'div',
                    { className: 'media-image' },
                    wp.element.createElement(
                        'div',
                        { className: 'image-thumbnail' },
                        wp.element.createElement(
                            'div',
                            { className: 'image-section' },
                            wp.element.createElement('img', { src: attributes.featuredImage, alt: '', className: 'img-thumbnail-square img-responsive img-loaded' })
                        )
                    )
                ),
                wp.element.createElement(
                    'div',
                    { className: 'lp-tc-related-inquiry-grades' },
                    wp.element.createElement(
                        'span',
                        null,
                        attributes.grade
                    )
                ),
                wp.element.createElement('div', { className: 'custom-bg-dark custom-bg-dark-inquiry-sets' }),
                wp.element.createElement(
                    'div',
                    { className: 'lp-tc-related-inquiry-set-description' },
                    wp.element.createElement(
                        'h4',
                        null,
                        attributes.title
                    )
                )
            )
        );
    }
});

/***/ })
/******/ ]);