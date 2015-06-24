app.controller("TeamVisionController", function ($scope, $http, $translate, teamVisionList, $sce) {

        var team_vision_list = teamVisionList;
        angular.forEach(team_vision_list, function(val, key){
            team_vision_list[key].TeamVision.modified = $sce.trustAsHtml(val.TeamVision.modified);
        });
        $scope.teamVisionList = team_vision_list;

    }
);
