zero-auth-demo
================

Installation
---

1. Clone repository

    ```bash
    git clone git@github.com:phuria/zero-auth-demo.git && cd zero-auth-demo
    ```

2. Create config.ini

    ```bash
    cp config.ini.dist config.ini
    ```

3. Create database schema

    ```bash
    php vendor/bin/doctrine orm:schema-tool:update --complete --force
    ```

4. Import data

    ```bash
    sudo mysql zeroauth < schema.sql
    ```

5. Run HTTP server

    ```bash
    cd web && php -S 0.0.0.0:8080
    ```

Client registration via CLI
---

```bash
php console.php register [username] [password]
```

Client registration via API
---

```
POST /user/?username=[username]&salt=[salt]&verifier=[verifier]
```

Client authorization via CLI
---

```bash
php console.php auth [username] [password]
```

Example output:

```
Public key generated: eadd91fa6dc19ae2d3ddbd46f7a9c949f5f47a77d8224d44ed6eff4ad051af5812203022fc48ca3d972f77be69e00b57b767c353fc37024a88deff1449f1a1ff6abf34ee5de634e41442afbe0f602e078c10c231b420c3ec7063eb85cae22e3b81383c403af1f2d13c3b9f18cd69222cfe65df887bfc3ec2ff0f8839f280b8a2
Private key generated: 716ee5b0054f84371c1dc9bfa016c322fa8283f6aadfba0b99af7e3fce3e97f1c568a1f972f6256f36a14f18c69273f8e66dd42703587793093d1b1f08794716
Sending public key to server.
Server sent following salt: cdcb13611d6ca91d502303e7610f0b416424c424d26c61e10ce2da280b5a991791c95b4d336ab2aebf6aea65385c4bc52d0b6cb073c022473696b604bd9cc0aa
Server sent following verifier: 300b963b8af43a0b6504319ca6cc857e50af9e3b1b2b2b7d6d0fc92b83f1c0f2483ddb7552cf1842b98a330867efb7ecaf41575dfffa40728a62c6942c972d35d69c892f08d802c33d509947283f3d2223ac3e42475de200d3a92cef5373280f3fc1c1cd168602e0b3c722716b425819dd303975b1b1b8fb50255b7858645b87
Server creates following session: 992f2e81403ba210efd753097445357b
Client computes following verifier: 300b963b8af43a0b6504319ca6cc857e50af9e3b1b2b2b7d6d0fc92b83f1c0f2483ddb7552cf1842b98a330867efb7ecaf41575dfffa40728a62c6942c972d35d69c892f08d802c33d509947283f3d2223ac3e42475de200d3a92cef5373280f3fc1c1cd168602e0b3c722716b425819dd303975b1b1b8fb50255b7858645b87
Server and client have same verifiers.
Server sent following public key: 6c189d4d8c96bda24cc9a88d6cf803a514f9e5a949a6ae5a1936d5ba85e6edb44b060c699a17dd621f86d0535fd019bc8e370fb129cf7892bd9cf743b2357f56d6363c7a1d8b7f5238e292e604a1018dd845a2590c5c5b904145c7fece5e8234bc0171464b5f1094f4a4f16976a29da7675d37bde09dd4bfee197cc81f5adf04
Computed server and client key scrambling: 52282a8f9f5629b6f31ca7f2e54d9271a22bba76
Computed session key: 6fe3d7ac017a865c714ee903c85069a640f08d9e
Computed client proof: ebf2f6d05bccecbd258632112b8644e3cc425998
Sending proof to server.
Server sent following proof: d60f7a74cc25df2b672a0250b367e49bdcbadd80
Client has been authenticated by server.
Computed server proof: d60f7a74cc25df2b672a0250b367e49bdcbadd80
Server proof is valid. Server has been authenticated by client.
Session is fully authorized.
You can now use following header: Authorization: Basic 992f2e81403ba210efd753097445357b:ebf2f6d05bccecbd258632112b8644e3cc425998
```

Client authorization via API
---

```
POST /user/[username]/session/[clientPublicKey]
```

```json
{  
  "username":"user",
  "verifier":"300b963b8af43a0b6504319ca6cc857e50af9e3b1b2b2b7d6d0fc92b83f1c0f2483ddb7552cf1842b98a330867efb7ecaf41575dfffa40728a62c6942c972d35d69c892f08d802c33d509947283f3d2223ac3e42475de200d3a92cef5373280f3fc1c1cd168602e0b3c722716b425819dd303975b1b1b8fb50255b7858645b87",
  "salt":"cdcb13611d6ca91d502303e7610f0b416424c424d26c61e10ce2da280b5a991791c95b4d336ab2aebf6aea65385c4bc52d0b6cb073c022473696b604bd9cc0aa",
  "serverPublicKey":"d563ac307cfe86f0082a974e572a679e83f01c8fcbcd63373e42f93b8ad808acf383c65b8e1052d5b309bbfe8b49187438a360949c269ba0269818d805ef52e2171531129c70ee3a57527f565963a358a1b30d315fb6e3351f4067cf96a6388430dd50fd1969ae3973c3bfa9202ce778b96d0cc18326ba4f9467295cf66ad41b",
  "session":{  
    "id":"f42173a6b06cbbfc5325a20474fce7d1",
    "uri":"\/session\/f42173a6b06cbbfc5325a20474fce7d1\/"
  }
}
```

```
POST /session/[sessionId]/auth/[clientProof]
```

```json
{  
  "id":"dc4380758e41233b4757e96694663714",
  "serverProof":"d0fbb73c49af2c00a1df042e992398a29f8de0c0",
  "header":"Authorization: Basic dc4380758e41233b4757e96694663714:cb312d21c3a2c8ec53781994617ba6f9abb325ae"
}
```

`Session key` - should be used to data encryption. 
Server and client known him, but him are not sent over network.

`Session id` - should be used as username in HTTP Basic Authorization header.

`Session client proof` - should be used as password in HTTP Basic Authorization header.

For more details about Remote Secure Protocol see [RFC5054](https://tools.ietf.org/html/rfc5054).

Product
---

### Product listing

```
GET /product/
```

__Query parameters:__
 - `after` - minimum product id
 - `before` - maximum product id 

```json
{
  "list": [
    {
      "id": 1,
      "title": "Fallout",
      "price": {
        "amount": 199,
        "currency": "USD"
      }
    },
    {
      "id": 2,
      "title": "Don't Starve",
      "price": {
        "amount": 299,
        "currency": "USD"
      }
    },
    {
      "id": 3,
      "title": "Baldur's Gate",
      "price": {
        "amount": 399,
        "currency": "USD"
      }
    }
  ],
  "cursor": {
    "after": 3,
    "before": 1,
    "prevUri": "/product/?before=1",
    "nextUri": "/product/?after=3"
  }
}
```

### Product insert

```
POST /product/
```

__Query parameters:__
 - `tile` (*) - product title
 - `priceAmount` - product price in cents (integer)
 - `priceCurrency` - product price currency
 
```json
{
  "id": 1
}
```

