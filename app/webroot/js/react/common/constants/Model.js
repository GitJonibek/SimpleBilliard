// バックエンドのモデルの定数をReactでも使う
export const Collaborator = Object.freeze({
  ApprovalStatus: {
    NEW: 0,
    REAPPLICATION:1,
    DONE:2,
    WITHDRAW:3
  },
  Type: {
    TYPE_COLLABORATOR: 0,
    OWNER: 1
  },
  Evaluation: {
    IS_NOT_TARGET: 0,
    IS_TARGET: 1
  },
  // 第一フェーズではリスト全件表示の仕様のためこれは使わない
  NUMBER_OF_DISPLAY_LIST_CARD: 10
})

export const TopKeyResult = Object.freeze({
  IS_NOT_CLEAR: 0,
  IS_CLEAR: 1,
  IS_NOT_IMPORTANT: 0,
  IS_IMPORTANT: 1
})
