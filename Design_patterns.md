# Phân Tích Thiết Kế Hướng Đối Tượng (OOP) trong Dự Án

Tài liệu này phân tích các mẫu thiết kế (Design Patterns) và nguyên tắc OOP được áp dụng trong dự án Laravel này, giúp làm rõ cấu trúc và logic của hệ thống.

## Mục lục

1.  [Kiến trúc 3 lớp (3-Tier Architecture)](#1-kiến-trúc-3-lớp-3-tier-architecture)
2.  [Repository Pattern](#2-repository-pattern)
3.  [Strategy Pattern: Hệ thống Chấm điểm Khách hàng](#3-strategy-pattern-hệ-thống-chấm-điểm-khách-hàng)
4.  [Factory & Adapter Pattern: Hệ thống Cache](#4-factory--adapter-pattern-hệ-thống-cache)
5.  [Observer Pattern: Xử lý sự kiện](#5-observer-pattern-xử-lý-sự-kiện)
6.  [Data Transfer Object (DTO) Pattern](#6-data-transfer-object-dto-pattern)
7.  [Hệ thống Thông báo Bất đồng bộ với Kafka](#7-hệ-thống-thông-báo-bất-đồng-bộ-với-kafka)

---

### 1. Kiến trúc 3 lớp (3-Tier Architecture)

Dự án tuân thủ chặt chẽ kiến trúc 3 lớp, phân tách rõ ràng các mối quan tâm (Separation of Concerns):

-   **Controller Layer (`app/Http/Controllers`)**: Lớp mỏng nhất, chỉ chịu trách nhiệm xử lý HTTP request và response. Nó nhận dữ liệu từ người dùng, gọi đến `Service Layer` để xử lý và trả về kết quả.
-   **Service Layer (`app/Services`)**: Nơi chứa toàn bộ logic nghiệp vụ (business logic) của ứng dụng. Các service điều phối hoạt động giữa các `Repository` và các thành phần khác.
-   **Repository Layer (`app/Repositories`)**: Lớp trừu tượng hóa việc truy cập dữ liệu. Nó đóng gói tất cả các câu lệnh truy vấn đến cơ sở dữ liệu (sử dụng Eloquent), giúp `Service Layer` không cần biết về chi tiết cách dữ liệu được lưu trữ và truy xuất.

**Ví dụ:** `OrderController` -> `OrderService` -> `OrderRepository`.

---

### 2. Repository Pattern

Đây là một trong những mẫu thiết kế nền tảng của dự án, giúp tách biệt logic nghiệp vụ khỏi tầng truy cập dữ liệu.

-   **Mục đích**: Che giấu sự phức tạp của việc truy vấn cơ sở dữ liệu và cung cấp một giao diện (interface) rõ ràng để thao tác với các đối tượng dữ liệu (Models).

-   **Cách áp dụng**:

    1.  **Contracts (`app/Contracts/*.php`)**: Các `interface` được định nghĩa để đặt ra "hợp đồng" cho mỗi repository. Ví dụ: `CustomerRepositoryInterface` định nghĩa các phương thức bắt buộc như `getActiveCustomers()`, `findByEmail()`.
    2.  **Repositories (`app/Repositories/*.php`)**: Các `class` cụ thể triển khai các interface trên. Ví dụ: `CustomerRepository` chứa logic truy vấn Eloquent để lấy dữ liệu khách hàng.
    3.  **Dependency Injection**: Trong `AppServiceProvider`, chúng ta "bind" interface với class triển khai cụ thể.
        ```php
        $this->app->bind(CustomerRepositoryInterface::class, CustomerRepository::class);
        ```
    4.  **Sử dụng**: Trong các `Service` (ví dụ: `CustomerService`), chúng ta "inject" `CustomerRepositoryInterface` qua constructor. Laravel sẽ tự động cung cấp đối tượng `CustomerRepository` tương ứng. Điều này tuân thủ nguyên tắc Dependency Inversion.

-   **Lợi ích**:
    -   Logic nghiệp vụ (trong Services) không bị phụ thuộc vào Eloquent.
    -   Dễ dàng thay đổi nguồn dữ liệu (ví dụ: từ MySQL sang một API khác) mà không ảnh hưởng đến Service.
    -   Dễ dàng viết unit test cho Service bằng cách "mock" Repository.

---

### 3. Strategy & Composite Pattern: Hệ thống Chấm điểm Khách hàng

Hệ thống chấm điểm và phân loại khách hàng là một ví dụ điển hình của việc kết hợp **Strategy Pattern** và **Composite Pattern**.

-   **Mục đích**: Cho phép định nghĩa một họ các thuật toán (Strategy) và cấu trúc chúng thành một cây (Composite) để client có thể đối xử với một thuật toán đơn lẻ hay một nhóm thuật toán một cách đồng nhất.

-   **Cách áp dụng**:

    1.  **Component Interface (`ScoringStrategyInterface`)**: Định nghĩa một giao diện chung cho tất cả các đối tượng trong cây. Nó khai báo phương thức `calculateScore()`. Cả thuật toán đơn lẻ và nhóm thuật toán đều triển khai interface này.
    2.  **Leaf - Concrete Strategies (`app/Services/ScoringStrategies/*.php`)**: Đây là các "lá" trong cây, đại diện cho các thuật toán tính điểm riêng lẻ.
        -   `TotalValueScoringStrategy`: Tính điểm dựa trên tổng giá trị đơn hàng.
        -   `OrderCountScoringStrategy`: Tính điểm dựa trên số lượng đơn hàng.
        -   `LocationScoringStrategy`: Tính điểm dựa trên vị trí địa lý.
    3.  **Composite (`CompositeScoringStrategy`)**: Đây là một "nhánh" trong cây. Nó cũng triển khai `ScoringStrategyInterface` nhưng bên trong nó chứa một tập hợp các đối tượng `ScoringStrategyInterface` khác (có thể là Leaf hoặc Composite khác). Phương thức `calculateScore()` của nó sẽ duyệt qua tất cả các strategy con, gọi `calculateScore()` của chúng và tổng hợp kết quả (ví dụ: tính tổng có trọng số).
    4.  **Context (`CustomerScoringService`)**: Class này giờ đây đơn giản hơn. Nó chỉ cần giữ một tham chiếu đến một đối tượng `ScoringStrategyInterface` duy nhất. Đối tượng này có thể là một strategy đơn giản hoặc một `CompositeScoringStrategy` phức tạp. `CustomerScoringService` không cần biết chi tiết bên trong, nó chỉ cần gọi `calculateScore()`.

-   **Lợi ích**:
    -   **Open/Closed Principle**: Dễ dàng thêm các quy tắc tính điểm mới (Leaf) hoặc các cách kết hợp mới (Composite) mà không cần sửa đổi `CustomerScoringService`.
    -   **Đơn giản hóa Client**: `CustomerScoringService` (client) không cần phải quản lý một danh sách các strategy. Logic duyệt và tổng hợp được đóng gói hoàn toàn trong `CompositeScoringStrategy`.
    -   **Linh hoạt**: Cho phép tạo ra các cấu trúc chấm điểm phức tạp bằng cách lồng các Composite vào nhau.
    -   Logic của mỗi thuật toán được đóng gói riêng, dễ hiểu và bảo trì.

---

### 4. Factory & Adapter Pattern: Hệ thống Cache

Hệ thống cache được xây dựng linh hoạt bằng cách kết hợp nhiều mẫu thiết kế.

-   **Mục đích**: Cung cấp một cách để tạo ra các đối tượng cache (Redis, Memcached) mà không cần chỉ định chính xác lớp của đối tượng sẽ được tạo.

-   **Cách áp dụng**:

    1.  **Interface (`CacheServiceInterface`)**: Định nghĩa các hành động cache cơ bản (`get`, `put`, `forget`, ...).
    2.  **Adapters (`RedisCacheService`, `MemcachedCacheService`)**: Đây là các class triển khai `CacheServiceInterface`. Mỗi class "chuyển đổi" (adapt) các lệnh của thư viện cache tương ứng (Redis, Memcached) thành các phương thức được định nghĩa trong `CacheServiceInterface`. Đây là một dạng của **Adapter Pattern**.
    3.  **Factory (`CacheFactory`)**: Class này có một phương thức tĩnh `make($driver)` chịu trách nhiệm tạo ra đối tượng cache service tương ứng (`RedisCacheService` hoặc `MemcachedCacheService`) dựa vào driver được cấu hình.
    4.  **Facade (`CacheManager`)**: Cung cấp một giao diện đơn giản và thuận tiện để tương tác với hệ thống cache. Nó sử dụng `CacheFactory` để lấy đối tượng cache và ủy quyền các cuộc gọi đến nó.

-   **Lợi ích**:
    -   Dễ dàng chuyển đổi giữa các hệ thống cache (ví dụ: từ Redis sang Memcached) chỉ bằng cách thay đổi cấu hình.
    -   Code nghiệp vụ chỉ cần tương tác với `CacheManager` hoặc `CacheServiceInterface`, không cần biết về chi tiết triển khai bên dưới.

---

### 5. Observer Pattern: Xử lý sự kiện

Dự án sử dụng hệ thống Event-Listener của Laravel, một hiện thực hóa của **Observer Pattern**, để giảm sự phụ thuộc trực tiếp giữa các module.

-   **Mục đích**: Định nghĩa một cơ chế "đăng ký" (subscribe) để nhiều đối tượng (listeners) có thể "lắng nghe" và phản ứng với các sự kiện xảy ra trên một đối tượng khác (event).

-   **Cách áp dụng**:

    1.  **Event (`OrderCreated`)**: Một class đơn giản chứa dữ liệu về sự kiện đã xảy ra (ví dụ: chứa thông tin đơn hàng vừa được tạo).
    2.  **Dispatching**: Trong `OrderService`, sau khi tạo đơn hàng thành công, một sự kiện được "bắn" ra:
        ```php
        Event::dispatch(new OrderCreated($order));
        ```
        `OrderService` không cần biết ai sẽ xử lý sự kiện này.
    3.  **Listener (`RecalculateCustomerScore`)**: Class này "đăng ký" để lắng nghe sự kiện `OrderCreated`. Khi sự kiện xảy ra, phương thức `handle()` của listener sẽ được tự động gọi. Listener này sau đó gọi đến `CustomerScoreUpdaterService` để cập nhật điểm cho khách hàng.

-   **Lợi ích**:
    -   **Loose Coupling**: `OrderService` và `CustomerScoreUpdaterService` không biết về sự tồn tại của nhau.
    -   **Single Responsibility**: Mỗi listener chỉ chịu trách nhiệm cho một tác vụ cụ thể.
    -   Dễ dàng mở rộng: Có thể thêm các hành động mới khi một đơn hàng được tạo (ví dụ: gửi thông báo, cập nhật kho,...) bằng cách tạo thêm các listener mới mà không cần sửa đổi `OrderService`.

---

### 6. Data Transfer Object (DTO) Pattern

DTO được sử dụng để đóng gói và truyền dữ liệu giữa các lớp một cách có cấu trúc.

-   **Mục đích**: Tạo ra các đối tượng đơn giản, không chứa logic nghiệp vụ, chỉ dùng để chứa dữ liệu.

-   **Cách áp dụng**:

    -   Class `CustomerScoringResultDTO` được sử dụng bởi `CustomerScoreUpdaterService` để trả về kết quả của việc cập nhật điểm.
    -   Nó là một `readonly class` chứa các thông tin như `customer`, `oldScore`, `newScore`, `wasReclassified`,...
    -   Thay vì trả về một mảng (array) với các key không xác định, DTO cung cấp một "hợp đồng" dữ liệu rõ ràng, giúp code dễ đọc và IDE có thể tự động gợi ý (autocompletion).

-   **Lợi ích**:
    -   Cải thiện tính rõ ràng và dễ bảo trì của code.
    -   Đảm bảo tính nhất quán của dữ liệu được truyền đi.
    -   Ngăn chặn việc thay đổi dữ liệu không mong muốn (nhờ `readonly`).

---

### 7. Hệ thống Thông báo Bất đồng bộ với Kafka

Một trong những điểm nâng cao của dự án là việc tách rời việc gửi thông báo (email) ra khỏi luồng xử lý chính của request tạo đơn hàng.

-   **Mục đích**: Tăng tốc độ phản hồi cho người dùng, tăng độ tin cậy và khả năng mở rộng của hệ thống.

-   **Cách áp dụng (Producer-Consumer Pattern)**:

    1.  **Producer (`KafkaProducerService`)**: Khi một đơn hàng được tạo trong `OrderService`, thay vì trực tiếp gửi email, nó chỉ gửi một message chứa thông tin đơn hàng đến một "topic" trên Kafka. Đây là một hành động không đồng bộ và rất nhanh.
    2.  **Consumer (`OrderNotificationConsumer`)**: Đây là một tiến trình (worker) chạy độc lập, liên tục lắng nghe các message mới từ topic của Kafka.
    3.  **Processing**: Khi nhận được message, `Consumer` sẽ gọi đến `OrderNotificationService` để thực hiện công việc tốn thời gian là gửi email xác nhận cho khách hàng.

-   **Lợi ích**:
    -   **Performance**: Người dùng nhận được phản hồi "Tạo đơn hàng thành công" gần như ngay lập tức mà không phải chờ đợi việc gửi email.
    -   **Reliability**: Nếu dịch vụ email tạm thời bị lỗi, message vẫn được lưu an toàn trong Kafka. `Consumer` có thể thử gửi lại sau đó, đảm bảo không có thông báo nào bị mất.
    -   **Scalability**: Nếu hệ thống có lượng đơn hàng lớn, ta có thể dễ dàng chạy nhiều tiến trình `Consumer` để xử lý song song.
