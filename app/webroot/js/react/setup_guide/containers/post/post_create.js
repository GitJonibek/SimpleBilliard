import React from 'react'
import { connect } from 'react-redux'
import {  } from '../../actions/post_actions'
import PostCreate from '../../components/post/post_create'

function mapStateToProps(state) {
  return state
}

function mapDispatchToProps(dispatch) {
  return {
  }
}

export default connect(mapStateToProps, mapDispatchToProps)(PostCreate);
