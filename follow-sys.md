# Follow System Implementation Plan
### Social Media Web App — High-Level Overview

---

## 1. Overview

The follow system enables users to subscribe to other users' content. A follower receives updates from the accounts they follow via their feed, notifications, and other discovery surfaces. This plan covers the core entities, API design, business logic, and key considerations for implementing a production-ready follow system.

---

## 2. Core Concepts

| Term | Description |
|---|---|
| **Follower** | The user who initiates the follow action |
| **Followee** | The user being followed |
| **Follow Request** | A pending follow for private accounts |
| **Mutual Follow** | Both users follow each other (friends) |

---

## 3. Data Model

### `follows` Table

```sql
CREATE TABLE follows (
  id          UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  follower_id UUID NOT NULL REFERENCES users(id),
  followee_id UUID NOT NULL REFERENCES users(id),
  status      ENUM('pending', 'accepted', 'blocked') NOT NULL DEFAULT 'accepted',
  created_at  TIMESTAMP NOT NULL DEFAULT NOW(),

  UNIQUE (follower_id, followee_id),
  CHECK (follower_id <> followee_id)
);

-- Indexes for fast lookups
CREATE INDEX idx_follows_follower ON follows(follower_id, status);
CREATE INDEX idx_follows_followee ON follows(followee_id, status);
```

### Derived Counters (Cached)
- `users.follower_count`
- `users.following_count`

These should be cached (e.g., in Redis) and updated asynchronously to avoid write bottlenecks.

---

## 4. API Design

| Method | Endpoint | Description |
|---|---|---|
| `POST` | `/users/:id/follow` | Follow a user |
| `DELETE` | `/users/:id/follow` | Unfollow a user |
| `GET` | `/users/:id/followers` | List a user's followers |
| `GET` | `/users/:id/following` | List accounts a user follows |
| `POST` | `/follow-requests/:id/accept` | Accept a pending follow request |
| `POST` | `/follow-requests/:id/decline` | Decline a pending follow request |
| `GET` | `/follow-requests` | List incoming follow requests (auth user) |

All endpoints require authentication. Responses should include pagination (cursor-based recommended for large lists).

---

## 5. Business Logic

### 5.1 Public vs. Private Accounts
- **Public account**: Follow is immediately accepted → status = `accepted`
- **Private account**: Follow creates a pending request → status = `pending` until the user accepts or declines

### 5.2 Blocking
- A blocked relationship prevents any follow or follow request from being created in either direction
- Blocked users should be excluded from follower/following lists

### 5.3 Self-Follow Prevention
- Enforced at both the application layer and the database (`CHECK` constraint)

### 5.4 Idempotency
- A duplicate follow request on an existing accepted follow should return a success response without creating a new record

---

## 6. Feed Integration

Following a user should trigger downstream updates to the follower's content feed. Two common patterns:

- **Fan-out on write (push)**: When a followee posts, their content is pushed into each follower's feed cache. Best for users with moderate follower counts.
- **Fan-out on read (pull)**: The feed is assembled at read time by pulling posts from followed accounts. Better for high-follower accounts (celebrities).

A **hybrid approach** is recommended: use push for regular users and pull for accounts above a follower threshold (e.g., >10k followers).

---

## 7. Notifications

Trigger notifications for the following events:

| Event | Recipient | Notification |
|---|---|---|
| New follower (public) | Followee | "X started following you" |
| New follow request (private) | Followee | "X requested to follow you" |
| Follow request accepted | Follower | "X accepted your follow request" |

Use an async queue (e.g., Redis/SQS + worker) to process notifications without blocking the API response.

---

## 8. Key UI States

The follow button should reflect these states clearly:

| State | Button Label |
|---|---|
| Not following | **Follow** |
| Request pending | **Requested** (with cancel option) |
| Following | **Following** (with unfollow option on hover) |
| Blocked | Button hidden or disabled |

---

## 9. Implementation Phases

### Phase 1 — Core Follow/Unfollow
- Database schema and indexes
- Follow and unfollow API endpoints
- Follower/following list endpoints
- Follow button UI with state management

### Phase 2 — Private Accounts & Requests
- Follow request flow (pending status)
- Accept/decline endpoints
- Incoming requests UI

### Phase 3 — Feed & Notifications
- Feed integration (fan-out strategy)
- Async notification pipeline
- In-app notification UI

### Phase 4 — Scale & Optimization
- Cached follower/following counts
- Cursor-based pagination
- Rate limiting on follow actions
- Block system integration

---

## 10. Non-Functional Considerations

- **Rate limiting**: Prevent follow-spam bots (e.g., max 200 follows/hour per user)
- **Privacy**: Follower lists of private accounts should only be visible to the account owner and mutual followers
- **Performance**: Follower/following counts should be served from cache, not computed on every request
- **Consistency**: Use background jobs to reconcile count caches periodically

---

*Last updated: February 2026*