<?php
App::uses('AppModel', 'Model');

/**
 * TopicSearchKeyword Model

 */
class TopicSearchKeyword extends AppModel
{

    /**
     * belongsTo associations
     *
     * @var array
     */
    public $belongsTo = [
        'Topic'
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public $validate = [
        'keywords' => [
            'notBlank' => [
                'rule' => ['notBlank'],
            ],
        ],
        'del_flg'  => [
            'boolean' => [
                'rule' => ['boolean'],
            ],
        ],
    ];

    /**
     * add keywords record for search
     * - in super exceptional, writing sql directory
     *  - For Reducing query cost
     * - All user name are separated by new line symbol
     *  - For searching by forward match
     *
     * @param  int $topicId
     *
     * @return bool
     */
    function add(int $topicId): bool
    {
        $query = <<<SQL
INSERT INTO topic_search_keywords (topic_id, team_id, keywords)
SELECT
    t.id as topic_id,
    t.team_id,
    CONCAT(
        '\n',
        GROUP_CONCAT(DISTINCT(u.last_name) SEPARATOR '\n'),
        '\n',
        GROUP_CONCAT(DISTINCT(u.first_name) SEPARATOR '\n'),
        '\n',
        GROUP_CONCAT(DISTINCT(ln.last_name) SEPARATOR '\n'),
        '\n',
        GROUP_CONCAT(DISTINCT(ln.first_name) SEPARATOR '\n')
        ) as keywords
FROM
    topics t
INNER JOIN topic_members tm ON
    t.id = tm.topic_id
INNER JOIN users u ON
    tm.user_id = u.id
LEFT JOIN local_names ln ON
    u.id = ln.user_id
WHERE t.id = $topicId
GROUP BY t.id
SQL;
        $this->query($query);
        return true;
    }

    /**
     * update by user id
     * - update all topics related by user
     * FIXME: DO NOT move now.
     *        error msg -> "Column not found: 1054 Unknown column 'tsk.topic_id' in 'on clause'"
     *
     * @param  [type] $userId [description]
     * @return bool           [description]
     */
    function updateByUserId($userId): bool
    {
        $query = <<<SQL
UPDATE topic_search_keywords tsk,
(SELECT
    CONCAT(
        '\n',
        GROUP_CONCAT(DISTINCT(u.last_name) SEPARATOR '\n'),
        '\n',
        GROUP_CONCAT(DISTINCT(u.first_name) SEPARATOR '\n'),
        '\n',
        GROUP_CONCAT(DISTINCT(ln.last_name) SEPARATOR '\n'),
        '\n',
        GROUP_CONCAT(DISTINCT(ln.first_name) SEPARATOR '\n')
    ) as keywords
    FROM topics t
    INNER JOIN topic_members tm ON
        t.id = tm.topic_id
    INNER JOIN users u ON
        u.id = tm.user_id
    LEFT JOIN local_names ln ON
        ln.user_id = u.id
) tsk2
INNER JOIN topics t ON
    tsk.topic_id = t.id
INNER JOIN topic_members tm ON
    t.id = tm.topic_id
INNER JOIN users u ON
    tm.user_id = u.id
SET tsk.keywords = tsk2.keywords
WHERE u.id = $userId
SQL;
        $this->query($query);
        return true;
    }
}
