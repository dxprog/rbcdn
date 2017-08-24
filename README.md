# RedditBooru CDN Content Retriever

A content manager to allow individual clients to run on the leanest amount of on-disk content as possible. When content is unavailable, this will attempt to pull from other clients or fall back on long-term storage such as an S3 store. Will also attempt to create mobile friendly images when a large image is being requested by a mobile client.