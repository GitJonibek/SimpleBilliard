var app = angular.module('myApp', ['ui.router', 'pascalprecht.translate', 'ui.bootstrap', 'jlareau.pnotify'])
    .run(['$rootScope', '$state', '$stateParams', '$http', '$translate',
        function ($rootScope, $state, $stateParams, $http, $translate) {

            $rootScope.$state = $state;
            $rootScope.$stateParams = $stateParams;

            $http.get(cake.url.j).success(function (data) {

                $rootScope.team_id = data.current_team_id;
                $rootScope.login_user_id = data.login_user_id;
                $rootScope.login_user_admin_flg = data.login_user_admin_flg;

                $rootScope.login_user_language = data.login_user_language;
                if ($rootScope.login_user_language === 'eng') {
                    $translate.use('en');
                }
                $rootScope.admin_user_cnt = data.admin_user_cnt;
            });
        }]
);


app.config(['$stateProvider', '$urlRouterProvider', '$translateProvider', '$httpProvider',
    function ($stateProvider, $urlRouterProvider, $translateProvider, $httpProvider) {

        $httpProvider.defaults.headers.common["X-Requested-With"] = 'XMLHttpRequest';

        $urlRouterProvider.otherwise("/");
        $stateProvider
            .state('member', {
                url: "/",
                templateUrl: "/template/team_member_list.html",
                controller: 'TeamMemberMainController'
            })
            .state('vision', {
                url: "/vision/:team_id",
                templateUrl: "/template/team_vision_list.html",
                controller: 'TeamVisionController',
                resolve: {
                    teamVisionList: ['$stateParams', '$http', function ($stateParams, $http) {
                        var request = {
                            method: 'GET',
                            url: cake.url.u + $stateParams.team_id
                        };
                        return $http(request).then(function (response) {
                            return response.data;
                        });

                    }]
                }

            })
            .state('vision_detail', {
                url: "/vision_detail/:team_vision_id/:active_flg",
                templateUrl: "/template/team_vision_detail.html",
                controller: 'TeamVisionDetailController',
                resolve: {
                    teamVisionDetail: ['$stateParams', '$http', function ($stateParams, $http) {

                        var active_flg = 0;
                        if ($stateParams.active_flg === 'true') {
                            active_flg = 1;
                        }

                        var request = {
                            method: 'GET',
                            url: cake.url.ac + $stateParams.team_vision_id + '/' + active_flg
                        };
                        return $http(request).then(function (response) {
                            return response.data;
                        });

                    }]
                }
            })
            .state('vision_archive', {
                url: "/vision_archive/:team_id/:active_flg",
                templateUrl: "/template/team_vision_list.html",
                controller: 'TeamVisionArchiveController',
                resolve: {
                    teamVisionArchiveList: ['$stateParams', '$http', function ($stateParams, $http) {
                        var request = {
                            method: 'GET',
                            url: cake.url.u + $stateParams.team_id + '/' + $stateParams.active_flg
                        };
                        return $http(request).then(function (response) {
                            return response.data;
                        });

                    }]
                }
            })
            .state('set_vision_archive', {
                url: "/set_vision_archive/:team_vision_id/:active_flg",
                resolve: {
                    setVisionArchive: ['$stateParams', '$http', function ($stateParams, $http) {
                        var request01 = {
                            method: 'GET',
                            url: cake.url.x
                        };
                        return $http(request01).then(function (response) {

                            if (response.data.is_admin_user === false) {
                                return false;
                            }

                            var request = {
                                method: 'GET',
                                url: cake.url.v + $stateParams.team_vision_id + '/' + $stateParams.active_flg
                            };
                            return $http(request).then(function (response) {
                                return response.data;
                            });
                        });

                    }]
                },
                controller: "TeamVisionSetArchiveController"
            })
            .state('vision_delete', {
                url: "/vision_delete/:team_vision_id",
                resolve: {
                    deleteVision: ['$stateParams', '$http', function ($stateParams, $http) {
                        var request01 = {
                            method: 'GET',
                            url: cake.url.x
                        };
                        return $http(request01).then(function (response) {

                            if (response.data.is_admin_user === false) {
                                return false;
                            }

                            var request02 = {
                                method: 'GET',
                                url: cake.url.w + $stateParams.team_vision_id
                            };
                            return $http(request02).then(function (response) {
                                return response;
                            });

                        });
                    }]
                },
                controller: "TeamVisionDeleteController"
            })
            .state('group_vision', {
                url: "/group_vision/:team_id",
                templateUrl: "/template/group_vision_list.html",
                resolve: {
                    LoginUserGroupId: ['$http', '$rootScope', function ($http, $rootScope) {
                        var request = {
                            method: 'GET',
                            url: cake.url.ab + $rootScope.team_id + '/' + $rootScope.login_user_id
                        };
                        return $http(request).then(function (response) {
                            return response.data;
                        });
                    }],
                    GroupVisionList: ['$stateParams', '$http', function ($stateParams, $http) {
                        var request = {
                            method: 'GET',
                            url: cake.url.y + $stateParams.team_id
                        };
                        return $http(request).then(function (response) {
                            return response.data;
                        });
                    }]
                },
                controller: "GroupVisionController"
            })
            .state('group_vision_archive', {
                url: "/group_vision_archive/:team_id/:active_flg",
                templateUrl: "/template/group_vision_list.html",
                resolve: {
                    GroupVisionArchiveList: ['$stateParams', '$http', function ($stateParams, $http) {
                        var request = {
                            method: 'GET',
                            url: cake.url.y + $stateParams.team_id + '/' + $stateParams.active_flg
                        };
                        return $http(request).then(function (response) {
                            return response.data;
                        });
                    }]
                },
                controller: 'GroupVisionArchiveController'
            })
            .state('set_group_vision_archive', {
                url: "/set_group_vision_archive/:group_vision_id/:active_flg",
                resolve: {
                    setGroupVisionArchive: ['$stateParams', '$http', function ($stateParams, $http) {
                        var request = {
                            method: 'GET',
                            url: cake.url.z + $stateParams.group_vision_id + '/' + $stateParams.active_flg
                        };
                        return $http(request).then(function (response) {
                            return response.data;
                        });
                    }]
                },
                controller: "GroupVisionSetArchiveController"
            })
            .state('group_vision_delete', {
                url: "/group_vision_delete/:group_vision_id",
                resolve: {
                    deleteVision: ['$stateParams', '$http', function ($stateParams, $http) {
                        var request02 = {
                            method: 'GET',
                            url: cake.url.aa + $stateParams.group_vision_id
                        };
                        return $http(request02).then(function (response) {
                            return response;
                        });

                    }]
                },
                controller: "GroupVisionDeleteController"
            })
            .state('group_vision_detail', {
                url: "/group_vision_detail/:group_vision_id/:active_flg",
                templateUrl: "/template/group_vision_detail.html",
                controller: 'GroupVisionDetailController',
                resolve: {
                    groupVisionDetail: ['$stateParams', '$http', function ($stateParams, $http) {

                        var active_flg = 0;
                        if ($stateParams.active_flg === 'true') {
                            active_flg = 1;
                        }

                        var request = {
                            method: 'GET',
                            url: cake.url.ad + $stateParams.group_vision_id + '/' + active_flg
                        };
                        return $http(request).then(function (response) {
                            return response.data;
                        });

                    }]
                }
            });

        $translateProvider.useStaticFilesLoader({
            prefix: '/i18n/locale-',
            suffix: '.json'
        });
        $translateProvider.preferredLanguage('ja');
        $translateProvider.fallbackLanguage('en');
    }]);
