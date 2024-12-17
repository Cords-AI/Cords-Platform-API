
## [1.0.0-beta.0](https://github.com/Cords-Connect/Partners-API/compare/v1.0.0...v1.0.0-beta.0) (2024-12-17)


### Features

* add ability to set account status ([79afa72](https://github.com/Cords-Connect/Partners-API/commit/79afa72d130b2dc8abde23d7035d719bb17f6e33))
* add access to routes through platform api keys ([91e57c4](https://github.com/Cords-Connect/Partners-API/commit/91e57c41f183288361426d60d247ee8764681324))
* add account search ([3aa2d08](https://github.com/Cords-Connect/Partners-API/commit/3aa2d081a736e8ad4b28b27b238e10c44e84f6ee))
* add admin get account by id ([d56a79c](https://github.com/Cords-Connect/Partners-API/commit/d56a79c0c5e879a49b9a340ab38dc57b885ed983))
* add api key onboarding ([c6656f2](https://github.com/Cords-Connect/Partners-API/commit/c6656f20b0bc7095a797419943f7e329f70bbd0f))
* add date range to account collection filters ([425733a](https://github.com/Cords-Connect/Partners-API/commit/425733ac70c425c85a05e6d939e4fd870392bd32))
* add email and admin filters ([b834c75](https://github.com/Cords-Connect/Partners-API/commit/b834c75ba67174e6e77b7586dd03ece24ce3617b))
* add export for logs ([7d7e7d4](https://github.com/Cords-Connect/Partners-API/commit/7d7e7d4bbb4acd40fd49e3ff889d57f7631ef2f6))
* add filter by status ([299bdec](https://github.com/Cords-Connect/Partners-API/commit/299bdec2ccac76ddc0c5c3e788613d72f0e3ae82))
* add pagination to /admin/users ([5370e6e](https://github.com/Cords-Connect/Partners-API/commit/5370e6e96b8f2577563278dfcd6b41a867b0dd88))
* add profile ([579ab23](https://github.com/Cords-Connect/Partners-API/commit/579ab23334ab1dcffd8eaaa7ccb361af1672e885))
* add sorting to /admin/users ([965a0e4](https://github.com/Cords-Connect/Partners-API/commit/965a0e4f30f9f7a24a47d316e889559e40431cca))
* add status to /authenticated/user ([90130d6](https://github.com/Cords-Connect/Partners-API/commit/90130d6bc4f096df8ab76012249fea3c295771b0))
* **deploy:** whitelist cords-widget.pages.dev ([129529e](https://github.com/Cords-Connect/Partners-API/commit/129529ecae64c29a28a8da1057a4f3628edbc046))
* fix getUser for users greater than limit ([6c3d23a](https://github.com/Cords-Connect/Partners-API/commit/6c3d23aaaa92cf10c9bcaca2208f6e5ceecec920))
* save filters ([aff0c51](https://github.com/Cords-Connect/Partners-API/commit/aff0c511a3837d0690dce01aea937e1bfd4fe5d3))
* send notifications to accounts@cords.ai ([87a1d0f](https://github.com/Cords-Connect/Partners-API/commit/87a1d0fa6815cfdf9a781ff79ad02efc6ac38859))
* validate dev keys only for 60 days ([4f526e1](https://github.com/Cords-Connect/Partners-API/commit/4f526e1e4364fff5c34f834dc773121b404cd675))


### Bug Fixes

* add misc fixes for sign in ([e3f17a7](https://github.com/Cords-Connect/Partners-API/commit/e3f17a7e5214cfc8bdf62419b1ff3af9d48e135b))
* create account on authenticate ([d04fd74](https://github.com/Cords-Connect/Partners-API/commit/d04fd7442cb1ed704fe620d2621a023455083133))
* create account when needed ([b6667e6](https://github.com/Cords-Connect/Partners-API/commit/b6667e6ad23f1baa125f9b78ff824fbdfc8c5003))
* make regex for api key valid referrer matching more strict ([b5d725b](https://github.com/Cords-Connect/Partners-API/commit/b5d725b321cd94bb1734692f4e298cd5f7c44a8e))
* set account on key creation ([99ede1c](https://github.com/Cords-Connect/Partners-API/commit/99ede1cafd50036c2df662a2866c41c5a12bb588))
* update province filter for multiple province selections ([856521a](https://github.com/Cords-Connect/Partners-API/commit/856521a9f11577c5110d94bcb418d84527b81ff6))

## 1.0.0 (2024-03-12)


### Features

* add ability to have multiple API keys ([4990533](https://github.com/Cords-Connect/Cords-Platform-API/commit/499053362e1bb19c8083f252b0f99d8c8cb22c0e))
* add admin/users endpoint ([80ab683](https://github.com/Cords-Connect/Cords-Platform-API/commit/80ab68327bb0aadecb7aba285be3b26042858c7c))
* add csrf token validation ([871386a](https://github.com/Cords-Connect/Cords-Platform-API/commit/871386a8f3f05845e541cf369df47e896a4dbef5))
* add Firebase session authenticator ([75f79fe](https://github.com/Cords-Connect/Cords-Platform-API/commit/75f79fe492446a0ccc90e59ae8b0e472b9ce369f))


### Bug Fixes

* fix authenticator with multiple cookies ([a9d28c5](https://github.com/Cords-Connect/Cords-Platform-API/commit/a9d28c5cf6398c219a8dd57167272e544b08219c))
* skip authenticator for validate path ([7803530](https://github.com/Cords-Connect/Cords-Platform-API/commit/7803530787e80e3c88c4dc29025c18e4befa0f77))
