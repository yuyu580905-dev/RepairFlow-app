# RepairFlow データベース設計書

## 1. 概要

RepairFlowのデータベース設計について、テーブル構成、カラム定義、制約、インデックスおよび設計方針を記載する。

本システムでは、主に以下の4テーブルで修理依頼業務を管理する。

| テーブル名            | 役割                     |
| --------------------- | ------------------------ |
| users                 | ユーザー情報・権限管理   |
| repair_requests       | 修理依頼情報             |
| repair_logs           | 修理対応・操作履歴       |
| cancellation_requests | キャンセル申請・審査履歴 |

Laravel標準の認証・システム管理用テーブルも使用する。

## 2. テーブル定義

### 2-1. users（ユーザー）

ユーザー情報およびシステム内の役割・利用状態を管理する。

| カラム名          | データ型        | NULL     | キー | デフォルト値   | 説明                       |
| ----------------- | --------------- | -------- | ---- | -------------- | -------------------------- |
| id                | BIGINT UNSIGNED | NOT NULL | PK   | AUTO_INCREMENT | ユーザーID                 |
| name              | VARCHAR(255)    | NOT NULL |      |                | ユーザー名                 |
| email             | VARCHAR(255)    | NOT NULL | UK   |                | メールアドレス             |
| email_verified_at | TIMESTAMP       | NULL     |      | NULL           | メール認証日時             |
| password          | VARCHAR(255)    | NOT NULL |      |                | ハッシュ化パスワード       |
| remember_token    | VARCHAR(100)    | NULL     |      | NULL           | ログイン状態保持用トークン |
| role              | VARCHAR(20)     | NOT NULL |      | requester      | ユーザー権限               |
| is_active         | BOOLEAN         | NOT NULL |      | TRUE           | アカウント有効状態         |
| created_at        | TIMESTAMP       | NULL     |      | NULL           | 作成日時                   |
| updated_at        | TIMESTAMP       | NULL     |      | NULL           | 更新日時                   |

※ MySQLではBOOLEANはTINYINT(1)の別名として扱われる。

**roleの管理値**

- requester：依頼者
- technician：技術者
- admin：管理者

**設計上の補足**

- emailにはユニーク制約を設定し、メールアドレスの重複登録を防止する。
- roleの初期値はrequesterとする。
- is_activeの初期値はTRUEとし、新規作成ユーザーを有効状態にする。
- パスワードはハッシュ化して保存する。

### 2-2. repair_requests（修理依頼）

依頼者が登録した修理依頼と、担当技術者・対応状況を管理する。

| カラム名     | データ型        | NULL     | キー | デフォルト値   | 説明           |
| ------------ | --------------- | -------- | ---- | -------------- | -------------- |
| id           | BIGINT UNSIGNED | NOT NULL | PK   | AUTO_INCREMENT | 修理依頼ID     |
| requester_id | BIGINT UNSIGNED | NOT NULL | FK   |                | 依頼者ID       |
| assigned_to  | BIGINT UNSIGNED | NULL     | FK   | NULL           | 担当技術者ID   |
| title        | VARCHAR(255)    | NOT NULL |      |                | 依頼件名       |
| description  | TEXT            | NOT NULL |      |                | 修理内容       |
| location     | VARCHAR(255)    | NOT NULL |      |                | 設置場所       |
| asset_name   | VARCHAR(255)    | NOT NULL |      |                | 対象設備名     |
| priority     | VARCHAR(20)     | NOT NULL |      | normal         | 優先度         |
| status       | VARCHAR(20)     | NOT NULL |      | received       | 対応ステータス |
| created_at   | TIMESTAMP       | NULL     |      | NULL           | 作成日時       |
| updated_at   | TIMESTAMP       | NULL     |      | NULL           | 更新日時       |

**priorityの管理値**

- low：低
- normal：通常
- high：高

**statusの管理値**

- received：受付
- in_progress：対応中
- on_hold：保留
- completed：完了
- cancelled：キャンセル

**外部キー**

| カラム名     | 参照先   | 削除時の動作 |
| ------------ | -------- | ------------ |
| requester_id | users.id | RESTRICT     |
| assigned_to  | users.id | RESTRICT     |

**インデックス**

| 対象カラム           | 目的                             |
| -------------------- | -------------------------------- |
| requester_id, status | 依頼者ごとのステータス別検索     |
| assigned_to, status  | 担当技術者ごとのステータス別検索 |
| status, created_at   | ステータス別の新着依頼検索       |

**設計上の補足**

- requester_idは必須とし、修理依頼の作成者を必ず記録する。
- assigned_toはNULLを許容し、担当技術者の未割当状態を表現する。
- priorityの初期値はnormalとする。
- statusの初期値はreceivedとする。
- 担当技術者には有効な技術者ユーザーのみを割り当てる。これはアプリケーション側で制御する。

### 2-3. repair_logs（修理履歴）

修理依頼に対する操作やステータス変更などの履歴を記録する。

| カラム名          | データ型        | NULL     | キー | デフォルト値      | 説明             |
| ----------------- | --------------- | -------- | ---- | ----------------- | ---------------- |
| id                | BIGINT UNSIGNED | NOT NULL | PK   | AUTO_INCREMENT    | 履歴ID           |
| repair_request_id | BIGINT UNSIGNED | NOT NULL | FK   |                   | 修理依頼ID       |
| user_id           | BIGINT UNSIGNED | NOT NULL | FK   |                   | 操作ユーザーID   |
| action            | VARCHAR(30)     | NOT NULL |      |                   | 操作種別         |
| message           | TEXT            | NOT NULL |      |                   | 操作内容         |
| from_status       | VARCHAR(20)     | NULL     |      | NULL              | 変更前ステータス |
| to_status         | VARCHAR(20)     | NULL     |      | NULL              | 変更後ステータス |
| created_at        | TIMESTAMP       | NOT NULL |      | CURRENT_TIMESTAMP | 記録日時         |

**actionの管理値**

- request_created：修理依頼作成
- technician_assigned：担当技術者割当
- status_changed：ステータス変更
- cancellation_requested：キャンセル申請
- cancellation_approved：キャンセル承認
- cancellation_rejected：キャンセル却下

**外部キー**

| カラム名          | 参照先             | 削除時の動作 |
| ----------------- | ------------------ | ------------ |
| repair_request_id | repair_requests.id | RESTRICT     |
| user_id           | users.id           | RESTRICT     |

**インデックス**

| 対象カラム                    | 目的                             |
| ----------------------------- | -------------------------------- |
| repair_request_id, created_at | 修理依頼ごとの履歴を時系列で取得 |

**設計上の補足**

- from_statusとto_statusは、ステータス変更を伴わない操作ではNULLを許容する。
- created_atはデータベース側で現在日時を設定する。
- 修理履歴はアプリケーション側で追記型として管理し、過去の操作記録を不用意に変更・削除しない。
- RepairLogモデルでは通常のupdated_at管理を行わず、created_atのみを使用する。

### 2-4. cancellation_requests（キャンセル申請）

依頼者からのキャンセル申請と、管理者による審査結果を管理する。

| カラム名          | データ型        | NULL     | キー | デフォルト値   | 説明             |
| ----------------- | --------------- | -------- | ---- | -------------- | ---------------- |
| id                | BIGINT UNSIGNED | NOT NULL | PK   | AUTO_INCREMENT | キャンセル申請ID |
| repair_request_id | BIGINT UNSIGNED | NOT NULL | FK   |                | 修理依頼ID       |
| requester_id      | BIGINT UNSIGNED | NOT NULL | FK   |                | 申請者ID         |
| reviewed_by       | BIGINT UNSIGNED | NULL     | FK   | NULL           | 審査者ID         |
| reason            | TEXT            | NOT NULL |      |                | キャンセル理由   |
| status            | VARCHAR(20)     | NOT NULL |      | pending        | 審査ステータス   |
| review_comment    | TEXT            | NULL     |      | NULL           | 審査コメント     |
| reviewed_at       | TIMESTAMP       | NULL     |      | NULL           | 審査日時         |
| created_at        | TIMESTAMP       | NULL     |      | NULL           | 作成日時         |
| updated_at        | TIMESTAMP       | NULL     |      | NULL           | 更新日時         |

**statusの管理値**

- pending：審査待ち
- approved：承認
- rejected：却下

**外部キー**

| カラム名          | 参照先             | 削除時の動作 |
| ----------------- | ------------------ | ------------ |
| repair_request_id | repair_requests.id | RESTRICT     |
| requester_id      | users.id           | RESTRICT     |
| reviewed_by       | users.id           | RESTRICT     |

**インデックス**

| 対象カラム                    | 目的                                 |
| ----------------------------- | ------------------------------------ |
| repair_request_id, created_at | 修理依頼ごとの申請履歴を時系列で取得 |
| status, created_at            | 審査ステータス別の申請検索           |
| repair_request_id, status     | 修理依頼ごとの審査状態検索           |

**設計上の補足**

- reviewed_byは審査前の状態を表現するためNULLを許容する。
- review_commentとreviewed_atは審査前には値を持たないためNULLを許容する。
- statusの初期値はpendingとする。
- 1つの修理依頼に対して複数のキャンセル申請を保持できる。
- 却下された場合も過去の申請を残し、再申請時には新しいレコードを作成することで申請履歴を保持する。

## 3. 制約・インデックス設計

### 3-1. 主キー

各テーブルのidを主キーとする。

主キーにはLaravelのid()による自動採番のBIGINT UNSIGNEDを使用する。

### 3-2. 外部キー

業務テーブル間の関連性を保証するため、外部キー制約を設定する。

外部キーの削除時動作はRESTRICTとし、関連する履歴データが不用意に削除されることを防止する。

### 3-3. ユニーク制約

users.emailにユニーク制約を設定し、メールアドレスの重複を防止する。

### 3-4. インデックス

検索・一覧表示で利用するカラムの組み合わせに複合インデックスを設定する。

インデックスは検索条件と並び替え条件を考慮して設計する。

## 4. データ整合性・業務ルール

ロール、ステータス、優先度、操作種別などは文字列カラムで管理する。

データベースのENUM型やCHECK制約ではなく、バリデーションやアプリケーションロジックによって許容値・変更条件を制御する。

**主な業務ルール**

- ユーザーのroleはrequester、technician、adminのいずれかとする。
- 無効化されたユーザーはログインできない。
- 修理依頼は依頼者本人のみが作成できる。
- 担当技術者には有効な技術者のみを割り当てる。
- キャンセル申請は依頼者本人の受付済み依頼に対して行う。
- キャンセル承認時は修理依頼のステータスをcancelledに変更する。
- キャンセル却下時は修理依頼のステータスを変更しない。
- 修理履歴は追記型で管理する。
- 最後の有効な管理者を無効化、または管理者以外のロールに変更することはできない。

## 5. 履歴データの保護方針

RepairFlowでは修理依頼の対応履歴とキャンセル申請履歴を重要な業務データとして扱う。

そのため、以下の方針を採用する。

1. repair_logsは操作のたびに新しいレコードを追加する。
2. cancellation_requestsは却下後の再申請時にも過去の申請を保持する。
3. 外部キーの削除時動作にRESTRICTを設定する。
4. 関連するユーザーや修理依頼の削除によって履歴が失われないようにする。

## 6. Laravel標準テーブル

以下はLaravelの標準機能で使用するテーブルであり、RepairFlowの主要な業務テーブルとは分けて管理する。

| テーブル名             | 用途                                  |
| ---------------------- | ------------------------------------- |
| password_reset_tokens  | パスワードリセット用トークン          |
| failed_jobs            | キュー処理に失敗したジョブの記録      |
| personal_access_tokens | Laravel Sanctumのアクセストークン管理 |

これらのテーブルはLaravelの標準機能に対応するものであり、主要な業務ER図の対象外とする。
