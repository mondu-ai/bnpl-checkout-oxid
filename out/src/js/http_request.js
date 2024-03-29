class HttpRequest {
  constructor(baseUrl = '', headers = { 'Content-Type': 'application/json' }) {
    this.baseUrl = baseUrl;
    this.headers = headers;
  }

  get(url, data = '') {
    return this.request(url, 'GET', data);
  }

  post(url, data = '') {
    return this.request(url, 'POST', data);
  }

  patch(url, data = '') {
    return this.request(url, 'PATCH', data);
  }

  put(url, data = '') {
    return this.request(url, 'PUT', data);
  }

  delete(url, data = '') {
    return this.request(url, 'DELETE', data);
  }

  async request(url, method, data) {
    let response = null;

    switch (method) {
      case 'POST':
      case 'PATCH':
      case 'PUT':
      case 'DELETE':
        if (typeof data === 'object') {
          url = this.baseUrl + url;
          response = await fetch(url, {
            method,
            headers: this.headers,
            body: JSON.stringify(data)
          });

          if (response.status > 302) {
            return response;
          } else {
            return {
              status: response.status,
              data: await response.json()
            };
          }
        } else {
          console.error('passed data should be object');
        }
        break;
      case 'GET':
        url = !!data ? this.url + url + data : this.baseUrl + url;
        response = await fetch(url, {
          method,
          headers: this.headers
        });
        break;
      default:
        console.error('Unsupported request type');
    }

    if (response.status > 302) {
      return response;
    } else {
      return {
        status: response.status,
        data: await response.json()
      };
    }
  }
}
