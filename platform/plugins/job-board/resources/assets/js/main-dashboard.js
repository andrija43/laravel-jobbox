'use strict'
;(function ($) {
    // Page loading
    $(window).on('load', function () {
        $('#preloader-active').fadeOut('slow')
    })
    /*-----------------
        Menu Stick
    -----------------*/
    let header = $('.sticky-bar')
    let win = $(window)
    win.on('scroll', function () {
        let scroll = win.scrollTop()
        if (scroll < 200) {
            header.removeClass('stick')
            $('.header-style-2 .categories-dropdown-active-large').removeClass('open')
            $('.header-style-2 .categories-button-active').removeClass('open')
        } else {
            header.addClass('stick')
        }
    })

    //sidebar sticky
    if ($('.sticky-sidebar').length) {
        $('.sticky-sidebar').theiaStickySidebar()
    }

    /*----------------------------
        Category toggle function
    ------------------------------*/
    if ($('.categories-button-active').length) {
        let searchToggle = $('.categories-button-active')
        searchToggle.on('click', function (e) {
            e.preventDefault()
            if ($(this).hasClass('open')) {
                $(this).removeClass('open')
                $(this).siblings('.categories-dropdown-active-large').removeClass('open')
            } else {
                $(this).addClass('open')
                $(this).siblings('.categories-dropdown-active-large').addClass('open')
            }
        })
    }
    /*---- CounterUp ----*/
    if ($('.count').length) {
        $('.count').counterUp({
            delay: 10,
            time: 2000,
        })
    }
    // Isotope active
    if ($('.grid').length) {
        $('.grid').imagesLoaded(function () {
            // init Isotope
            $('.grid').isotope({
                itemSelector: '.grid-item',
                percentPosition: true,
                layoutMode: 'masonry',
                masonry: {
                    // use outer width of grid-sizer for columnWidth
                    columnWidth: '.grid-item',
                },
            })
        })
    }

    /*====== SidebarSearch ======*/
    function sidebarSearch() {
        let searchTrigger = $('.search-active'),
            endTriggersearch = $('.search-close'),
            container = $('.main-search-active')
        searchTrigger.on('click', function (e) {
            e.preventDefault()
            container.addClass('search-visible')
        })
        endTriggersearch.on('click', function () {
            container.removeClass('search-visible')
        })
    }

    sidebarSearch()

    /*====== Sidebar menu Active ======*/
    function mobileHeaderActive() {
        let navbarTrigger = $('.burger-icon'),
            endTrigger = $('.mobile-menu-close'),
            container = $('.mobile-header-active'),
            wrapper4 = $('body')
        wrapper4.prepend('<div class="body-overlay-1"></div>')
        navbarTrigger.on('click', function (e) {
            navbarTrigger.toggleClass('burger-close')
            e.preventDefault()
            container.toggleClass('sidebar-visible')
            wrapper4.toggleClass('mobile-menu-active')
        })
        endTrigger.on('click', function () {
            container.removeClass('sidebar-visible')
            wrapper4.removeClass('mobile-menu-active')
        })
        $('.body-overlay-1').on('click', function () {
            container.removeClass('sidebar-visible')
            wrapper4.removeClass('mobile-menu-active')
            navbarTrigger.removeClass('burger-close')
        })
    }

    mobileHeaderActive()
    /*---------------------
        Mobile menu active
    ------------------------ */
    let $offCanvasNav = $('.mobile-menu'),
        $offCanvasNavSubMenu = $offCanvasNav.find('.sub-menu')
    /*Add Toggle Button With Off Canvas Sub Menu*/
    $offCanvasNavSubMenu.parent().prepend('<span class="menu-expand"><i class="fi-rr-angle-small-down"></i></span>')
    /*Close Off Canvas Sub Menu*/
    $offCanvasNavSubMenu.slideUp()
    /*Category Sub Menu Toggle*/
    $offCanvasNav.on('click', 'li a, li .menu-expand', function (e) {
        let $this = $(this)
        if (
            $this
                .parent()
                .attr('class')
                .match(/\b(menu-item-has-children|has-children|has-sub-menu)\b/) &&
            ($this.attr('href') === '#' || $this.hasClass('menu-expand'))
        ) {
            e.preventDefault()
            if ($this.siblings('ul:visible').length) {
                $this.parent('li').removeClass('active')
                $this.siblings('ul').slideUp()
            } else {
                $this.parent('li').addClass('active')
                $this.closest('li').siblings('li').removeClass('active').find('li').removeClass('active')
                $this.closest('li').siblings('li').find('ul:visible').slideUp()
                $this.siblings('ul').slideDown()
            }
        }
    })
    /*--- language currency active ----*/
    $('.mobile-language-active').on('click', function (e) {
        e.preventDefault()
        $('.lang-dropdown-active').slideToggle(900)
    })
    /*--- categories-button-active-2 ----*/
    $('.categories-button-active-2').on('click', function (e) {
        e.preventDefault()
        $('.categori-dropdown-active-small').slideToggle(900)
    })
    /*--- Mobile demo active ----*/
    let demo = $('.tm-demo-options-wrapper')
    $('.view-demo-btn-active').on('click', function (e) {
        e.preventDefault()
        demo.toggleClass('demo-open')
    })
    /*-----More Menu Open----*/
    $('.more_slide_open').slideUp()
    $('.more_categories').on('click', function () {
        $(this).toggleClass('show')
        $('.more_slide_open').slideToggle()
    })
    /* --- SwiperJS --- */
    $('.swiper-group-10').each(function () {
        new Swiper(this, {
            spaceBetween: 20,
            slidesPerView: 10,
            slidesPerGroup: 2,
            loop: true,
            navigation: {
                nextEl: '.swiper-button-next',
                prevEl: '.swiper-button-prev',
            },
            pagination: {
                el: '.swiper-pagination-1',
                type: 'custom',
                renderCustom: function (swiper, current, total) {
                    let customPaginationHtml = ''
                    for (let i = 0; i < total; i++) {
                        //Determine which pager should be activated at this time
                        if (i == current - 1) {
                            customPaginationHtml +=
                                '<span class="swiper-pagination-customs swiper-pagination-customs-active"></span>'
                        } else {
                            customPaginationHtml += '<span class="swiper-pagination-customs"></span>'
                        }
                    }
                    return customPaginationHtml
                },
            },
            autoplay: {
                delay: 10000,
            },
            breakpoints: {
                1199: {
                    slidesPerView: 10,
                },
                800: {
                    slidesPerView: 8,
                },
                600: {
                    slidesPerView: 6,
                },
                400: {
                    slidesPerView: 4,
                },
                250: {
                    slidesPerView: 2,
                },
            },
        })
    })

    $('.swiper-group-1').each(function () {
        new Swiper(this, {
            spaceBetween: 30,
            slidesPerView: 1,
            loop: true,
            navigation: {
                nextEl: '.swiper-button-next-1',
                prevEl: '.swiper-button-prev-1',
            },
            pagination: {
                el: '.swiper-pagination-1',
                type: 'custom',
                renderCustom: function (swiper, current, total) {
                    let customPaginationHtml = ''
                    for (let i = 0; i < total; i++) {
                        //Determine which pager should be activated at this time
                        if (i == current - 1) {
                            customPaginationHtml +=
                                '<span class="swiper-pagination-customs swiper-pagination-customs-active"></span>'
                        } else {
                            customPaginationHtml += '<span class="swiper-pagination-customs"></span>'
                        }
                    }
                    return customPaginationHtml
                },
            },
            autoplay: {
                delay: 10000,
            },
        })
    })

    $('.list-tags-job .remove-tags-job').on('click', function (e) {
        e.preventDefault()
        $(this).closest('.job-tag').remove()
    })
    // Video popup
    if ($('.popup-youtube').length) {
        $('.popup-youtube').magnificPopup({
            type: 'iframe',
            mainClass: 'mfp-fade',
            removalDelay: 160,
            preloader: false,
            fixedContentPos: false,
        })
    }

    $('.btn-expanded').on('click', function () {
        if ($(this).hasClass('btn-collapsed')) {
            $(this).removeClass('btn-collapsed')
            $('div.nav').removeClass('close-nav')
        } else {
            $(this).addClass('btn-collapsed')
            $('div.nav').addClass('close-nav')
        }
    })

    $('document').on('click', '.dropdown-menu > li > a', function () {
        location.href = this.href
    })

    $(document).ready(function () {
        const $profileCompletedBox = $('#circle-staticstic-profile-completed')
        const percentage = $profileCompletedBox.data('percent-completed')

        console.log($profileCompletedBox.data('color'))

        $profileCompletedBox.circliful({
            animation: 1,
            foregroundBorderWidth: 10,
            backgroundBorderWidth: 10,
            percent: percentage,
            percentageTextSize: 20,
            textStyle: "font-size: 20px; font-weight: bold; font-family: 'Plus Jakarta Sans', sans-serif",
            fontColor: '#05264E',
            foregroundColor: $profileCompletedBox.data('color') ? $profileCompletedBox.data('color') : '#3498DB',
            fillColor: '#d8e0fd',
            backgroundColor: '#d8e0fd',
            multiPercentage: 0,
            targetTextSize: 20,
        })

        const refreshCoupon = (url) => {
            $.ajax({
                url: url,
                type: 'GET',
                success: ({ error, message, data }) => {
                    if (error) {
                        Botble.showError(message)

                        return
                    }

                    $('.order-detail-box').html(data)

                    Botble.showSuccess(message)
                },
                error: (error) => {
                    Botble.handleError(error)
                },
            })
        }

        $(document)
            .on('click', '.toggle-coupon-form', () => $(document).find('.coupon-form').toggle('fast'))
            .on('click', '.apply-coupon-code', (e) => {
                e.preventDefault()

                const $button = $(e.currentTarget)

                $.ajax({
                    url: $button.data('url'),
                    type: 'POST',
                    data: {
                        coupon_code: $button.closest('form').find('input[name="coupon_code"]').val(),
                    },
                    beforeSend: () => {
                        $button.addClass('button-loading')
                    },
                    success: ({ error, message }) => {
                        if (error) {
                            Botble.showError(message)

                            return
                        }

                        Botble.showSuccess(message)

                        const refreshUrl = $('.order-detail-box').data('refresh-url')
                        refreshCoupon(refreshUrl)
                    },
                    error: (error) => {
                        Botble.handleError(error)
                    },
                    complete: () => {
                        $button.removeClass('button-loading')
                    },
                })
            })
            .on('click', '.remove-coupon-code', (e) => {
                e.preventDefault()

                const $button = $(e.currentTarget)

                $.ajax({
                    url: $button.data('url'),
                    type: 'POST',
                    beforeSend: () => {
                        $button.addClass('button-loading')
                    },
                    success: ({ message, error }) => {
                        if (error) {
                            Botble.showError(message)

                            return
                        }

                        Botble.showSuccess(message)

                        const refreshUrl = $('.order-detail-box').data('refresh-url')
                        refreshCoupon(refreshUrl)
                    },
                    error: (error) => {
                        Botble.handleError(error)
                    },
                    complete: () => {
                        $button.removeClass('button-loading')
                    },
                })
            })
    })
})(jQuery)
