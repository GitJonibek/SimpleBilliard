import axios from "axios";
import FormData from "form-data";

export function getBaseUrl() {
  // テストにおけるモックのURLを定義
  if (typeof cake === "undefined") {
    return 'http://localhost'
  } else {
    return cake.url.route_url
  }
}

export function getCsrfTokenKey() {
  if (typeof cake === "undefined") {
    return 'csrf_token_key'
  } else {
    return cake.data.csrf_token.key
  }
}

export function post(uri, data, options, success_callback, error_callback) {
  return save(uri, data, options, success_callback, error_callback, "post")
}

export function del(uri, data, options, success_callback, error_callback) {
  return save(uri, data, options, success_callback, error_callback, "delete")
}

export function save(uri, data, options, success_callback, error_callback, request_method = "post") {
  options = options || {}
  const base_options = {
    headers: {
      'X-Requested-With': 'XMLHttpRequest'
    },
    dataType: 'json'
  }
  options = Object.assign(base_options, options)

  /* Create request parameter */
  const csrf_token_key = getCsrfTokenKey()
  const post_data = Object.assign({
    'data[_Token][key]': csrf_token_key
  }, data)
  const form_data = createFormData(post_data, ['photo'])
  const url = getBaseUrl() + uri;

  switch (request_method) {
    case "post":
      return axios.post(url, form_data, options)
        .then(success_callback, error_callback)

    case "put":
      return axios.put(url, form_data, options)
        .then(success_callback, error_callback)

    case "delete":
      return axios.delete(url, form_data, options)
        .then(success_callback, error_callback)
  }
}

/**
 * Create FormData
 *
 * @param data
 * @param directAppendKeys: Even if data is array or hash, direct append data.
 * @param formData
 * @param baseKey
 * @returns {*}
 */
export function createFormData(data, directAppendKeys = [], formData = null, baseKey = "") {
  if (!formData) {
    formData = new FormData()
  }

  for (const key in data) {
    let formKey = baseKey ? `${baseKey}[${key}]` : key;
    if (directAppendKeys.length > 0 && directAppendKeys.indexOf(formKey) != -1) {
      formData.append(formKey, data[key])
    } else if (Array.isArray(data[key])) {
      formData = createFormData(data[key], directAppendKeys, formData, formKey)
    } else if (data[key] instanceof Object) {
      formData = createFormData(data[key], directAppendKeys, formData, formKey)
    } else {
      const val = data[key] == null ? "" : data[key]
      formData.append(formKey, val)
    }
  }
  return formData
}
