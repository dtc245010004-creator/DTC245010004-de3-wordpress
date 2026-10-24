# Website giới thiệu sản phẩm An Nhiên

**Đề tài:** Đề 3 - Website quảng bá sản phẩm bằng WordPress  
**Repository:** [DTC245010004-de3-wordpress](https://github.com/dtc245010004-creator/DTC245010004-de3-wordpress)

Dự án dùng Docker Compose để chạy WordPress, MySQL, phpMyAdmin và Nginx reverse proxy. Giao diện An Nhiên quảng bá trà Việt; MU plugin tự tạo trang chủ cùng ba sản phẩm mẫu: Trà Shan Tuyết Hà Giang, Trà sen Tây Hồ và chè xanh Tân Cương Thái Nguyên.

## Khởi chạy trên Windows

Cần Docker Desktop ở chế độ Linux containers và PowerShell. Tại thư mục repository, chạy:

```powershell
.\scripts\setup.ps1 -Start
docker compose ps
.\scripts\demo.ps1
```

`setup.ps1 -Start` tạo `.env` nếu cần, kiểm tra cấu hình, khởi động các dịch vụ và chờ healthcheck. Lệnh này cũng đồng bộ mật khẩu với MySQL và Grafana đang lưu trong Docker volume, rồi chuẩn bị tài khoản quản trị WordPress. Dữ liệu hiện có được giữ nguyên.

Mở các địa chỉ sau:

| Thành phần | URL | Đăng nhập |
| --- | --- | --- |
| Website An Nhiên | http://localhost:8080 | `teacher` / `teacher123` |
| phpMyAdmin | http://localhost:8080/db-admin/ | `wpuser` / `wp123456` |
| Grafana | http://localhost:3001 | `admin` / `admin123` |
| Prometheus | http://localhost:9090 | Chỉ mở trên máy host local |

Các mật khẩu trên được đặt cố định để tiện trình bày trong lớp và chỉ nên dùng trên máy local. Không dùng chúng cho máy chủ công khai. `setup.ps1 -Start` giữ nguyên mật khẩu root và tài khoản nội bộ của exporter, đồng thời cập nhật tài khoản đăng nhập trên các trang trên cả khi named volume đã tồn tại. `.env` vẫn bị Git bỏ qua.

## Các dịch vụ

| Dịch vụ | Image | Vai trò |
| --- | --- | --- |
| WordPress | `wordpress:7.1-php8.3-apache` | CMS và trang giới thiệu sản phẩm |
| MySQL | `mysql:8.4` | Lưu nội dung WordPress |
| phpMyAdmin | `phpmyadmin:5.2-apache` | Giao diện quản trị cơ sở dữ liệu |
| Nginx | `nginxinc/nginx-unprivileged:1.29-alpine` | Reverse proxy, security headers |
| Prometheus | `prom/prometheus:v3.14.0` | Thu thập metrics mỗi 15 giây |
| Grafana | `grafana/grafana:13.2.1` | Dashboard tổng quan và cảnh báo |
| cAdvisor | `ghcr.io/google/cadvisor:v0.60.6` | Metrics CPU, RAM của container |
| Exporters | Nginx Exporter, MySQL Exporter | Metrics Nginx và MySQL |
| Loki | `grafana/loki:3.7.8` | Lưu log cục bộ tối đa 7 ngày |
| Promtail | `grafana/promtail:3.6.8` | Đọc log container và gửi tới Loki |

Nginx là cổng web duy nhất. Cơ sở dữ liệu, WordPress và phpMyAdmin nằm trong mạng backend nội bộ, không xuất bản cổng trực tiếp ra máy host.

## Cấu trúc repository

```text
compose.yaml                         Docker Compose
db/init/                             Tài khoản metrics chỉ đọc cho MySQL exporter
nginx/default.conf                   Reverse proxy và security headers
wp-content/mu-plugins/               Tạo nội dung demo và shortcode sản phẩm
wp-content/themes/dean3-store/       Giao diện storefront
scripts/setup.ps1                    Đồng bộ tài khoản demo và khởi động Compose
scripts/provision-wordpress.php      Tạo hoặc cập nhật tài khoản quản trị WordPress
scripts/demo.ps1                     Kiểm tra URL và scrape target khi stack chạy
prometheus/, grafana/, loki/, promtail/  Cấu hình giám sát và logging
docs/                                Báo cáo và tài liệu minh chứng
```

## Cấu hình reverse proxy

Nginx nhận HTTP tại `127.0.0.1:8080`, chuyển request tới WordPress và định tuyến `/db-admin/` tới phpMyAdmin. Reverse proxy giữ nguyên `Host` kèm cổng từ request để WordPress tạo URL chính tắc đúng, đồng thời gửi địa chỉ client và scheme tới upstream. Nginx áp dụng `X-Content-Type-Options`, `X-Frame-Options`, `Referrer-Policy` và `Permissions-Policy`. Các response header này phù hợp cho demo HTTP nội bộ; triển khai công khai cần thêm TLS và điều chỉnh hostname.

## Giám sát với Prometheus và Grafana

Prometheus scrape mỗi 15 giây. Nginx Exporter đọc `stub_status` trên cổng 8081 trong mạng Docker nội bộ; MySQL Exporter dùng tài khoản chỉ đọc; cAdvisor lấy CPU và RAM container. Các exporter không publish cổng lên máy host. Prometheus lưu dữ liệu trong named volume trong 7 ngày.

Dashboard `De An 3 - System overview` được tự động provision trong Grafana. Dashboard hiển thị số request Nginx, CPU/RAM container, tốc độ truy vấn MySQL và trạng thái các scrape target. Rule `ScrapeTargetDown` cảnh báo khi target không trả metrics liên tục trong 2 phút.

## Câu lệnh truy vấn Prometheus

```promql
up
sum(rate(nginx_http_requests_total[5m]))
sum by (name) (rate(container_cpu_usage_seconds_total{image!=""}[5m]))
sum by (name) (container_memory_working_set_bytes{image!=""})
rate(mysql_global_status_queries[5m])
```

## Log tập trung với Loki và Promtail

Promtail tìm container đang chạy qua Docker service discovery, đọc log JSON của Docker và gửi log tới Loki. Loki dùng TSDB schema v13 với filesystem storage; log được giữ 7 ngày. Grafana đã được provision sẵn datasource Loki. Không publish Loki hay Promtail ra host.

Trong Grafana, mở **Explore**, chọn datasource **Loki**, rồi chạy các LogQL query:

```logql
{job="docker"}
{job="docker", container=~".*wordpress.*"}
{job="docker"} |= "error"
```

Promtail cần đọc Docker API và file log của Docker để ánh xạ tên container tới log stream. Cấu hình chỉ mount socket và thư mục log ở chế độ read-only, nhưng quyền truy cập socket vẫn là quyền nhạy cảm; chỉ dùng stack này trên máy demo tin cậy và không xuất bản network metrics/logging ra ngoài.

**Lưu ý vòng đời:** Promtail đã hết hỗ trợ từ 2 tháng 3 năm 2026. Nó được giữ trong stack này để đáp ứng rubric của Đề 3; với hệ thống sử dụng lâu dài, thay agent bằng [Grafana Alloy](https://grafana.com/docs/loki/latest/send-data/promtail/) và chuyển cấu hình theo [hướng dẫn migration chính thức](https://grafana.com/docs/grafana-cloud/send-data/alloy/set-up/migrate/from-promtail/).

## Bảo vệ thông tin và dịch vụ

- Tài khoản trình bày WordPress, phpMyAdmin và Grafana dùng mật khẩu demo cố định; chỉ dùng trên máy local. Mật khẩu root DB và exporter vẫn được sinh cục bộ; `.env` bị loại trừ bằng `.gitignore`.
- Tài khoản `exporter` chỉ có `PROCESS`, `REPLICATION CLIENT`, `SELECT`; WordPress dùng tài khoản chỉ có quyền trên database ứng dụng.
- Cổng DB và phpMyAdmin không được publish ra host; backend network là `internal: true`.
- Nginx chạy bằng image unprivileged, filesystem chỉ đọc và bỏ toàn bộ Linux capabilities.
- Các dịch vụ bật `no-new-privileges`; Nginx chỉ lắng nghe trên loopback host.

## Dữ liệu

`db_data` và `wordpress_data` là named volumes. Dừng stack bằng `docker compose down`; dữ liệu còn nguyên. Để xóa dữ liệu demo cùng toàn bộ database, chạy `docker compose down -v`.

## Các mốc commit theo yêu cầu

1. Reverse proxy Nginx và website WordPress kết nối MySQL.
2. Prometheus + Grafana thu thập metrics container, Nginx và MySQL.
3. Loki + Promtail tập trung logs và truy vấn bằng LogQL.

Commit hiện có lần lượt chứa website + Nginx, Prometheus + Grafana và Loki + Promtail. Repository công khai: https://github.com/dtc245010004-creator/DTC245010004-de3-wordpress. Báo cáo có thông tin cá nhân được nộp riêng, không nằm trong repository.

