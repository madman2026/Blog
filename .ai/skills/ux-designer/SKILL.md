---
name: "ux-designer"
description: "Activates when user requests UX design guidance, design system architecture, responsive design, accessibility (WCAG), or usability testing. Do NOT use for backend API implementation. Examples: 'Improve form usability', 'Check WCAG compliance'."
---

# UX Designer Skill

## 🧠 Expertise

資深用戶體驗設計師，專精於設計系統、響應式設計、無障礙設計與用戶研究。

---

## 1. 設計系統架構

### 1.1 設計系統結構

```
Design System
├── Foundations (基礎)
│   ├── Colors (色彩)
│   ├── Typography (字型)
│   ├── Spacing (間距)
│   └── Breakpoints (響應式斷點)
├── Components (組件)
│   ├── Atoms (原子)
│   ├── Molecules (分子)
│   └── Organisms (有機體)
└── Patterns (模式)
    ├── Navigation (導航)
    ├── Forms (表單)
    └── Feedback (回饋)
```

### 1.2 色彩系統

```css
:root {
  /* 主色 */
  --color-primary-50: #E3F2FD;
  --color-primary-500: #2196F3;
  --color-primary-900: #0D47A1;
  
  /* 語意色 */
  --color-success: #4CAF50;
  --color-warning: #FF9800;
  --color-error: #F44336;
  --color-info: #2196F3;
  
  /* 中性色 */
  --color-gray-100: #F5F5F5;
  --color-gray-500: #9E9E9E;
  --color-gray-900: #212121;
}
```

### 1.3 間距系統 (8px Grid)

| Token | Value | 用途 |
|-------|-------|------|
| `--space-1` | 4px | 緊湊元素 |
| `--space-2` | 8px | 小間距 |
| `--space-3` | 12px | 中小間距 |
| `--space-4` | 16px | 標準間距 |
| `--space-6` | 24px | 區塊間距 |
| `--space-8` | 32px | 大間距 |

---

## 2. 組件設計原則

### 2.1 原子設計層級

| 層級 | 說明 | 範例 |
|-----|------|------|
| **Atoms** | 最小單位 | Button, Input, Icon |
| **Molecules** | 原子組合 | SearchBar, FormField |
| **Organisms** | 分子組合 | Header, Card, DataTable |
| **Templates** | 頁面結構 | DashboardLayout |
| **Pages** | 完整頁面 | UserProfilePage |

### 2.2 按鈕設計規範

```css
/* 按鈕層級 */
.btn-primary { /* 主要行動 */ }
.btn-secondary { /* 次要行動 */ }
.btn-tertiary { /* 第三級行動 */ }
.btn-ghost { /* 幽靈按鈕 */ }

/* 按鈕狀態 */
.btn:hover { /* 懸停 */ }
.btn:active { /* 點擊 */ }
.btn:focus { /* 聚焦 */ }
.btn:disabled { /* 禁用 */ }

/* 按鈕尺寸 */
.btn-sm { height: 32px; padding: 0 12px; }
.btn-md { height: 40px; padding: 0 16px; }
.btn-lg { height: 48px; padding: 0 24px; }
```

---

## 3. 響應式設計

### 3.1 斷點系統 (Mobile First)

```css
/* 斷點定義 */
--breakpoint-sm: 640px;   /* 手機橫向 */
--breakpoint-md: 768px;   /* 平板 */
--breakpoint-lg: 1024px;  /* 小桌面 */
--breakpoint-xl: 1280px;  /* 桌面 */
--breakpoint-2xl: 1536px; /* 大桌面 */

/* 使用方式 */
@media (min-width: 768px) {
  .container { max-width: 720px; }
}
```

### 3.2 響應式策略

| 策略 | 說明 |
|-----|------|
| **Fluid Grids** | 使用百分比而非固定寬度 |
| **Flexible Images** | `max-width: 100%` |
| **Media Queries** | 根據視口調整樣式 |
| **Content Priority** | 行動優先顯示重要內容 |

### 3.3 觸控友善設計

| 元素 | 最小尺寸 | 建議尺寸 |
|-----|---------|---------|
| **觸控目標** | 44px × 44px | 48px × 48px |
| **間距** | 8px | 12px |
| **字體** | 16px | 16px (避免縮放) |

---

## 4. 無障礙設計 (WCAG 2.1)

### 4.1 四大原則

| 原則 | 說明 |
|-----|------|
| **可感知** | 內容可被感知 (文字替代、對比度) |
| **可操作** | 介面可被操作 (鍵盤導航) |
| **可理解** | 內容可被理解 (一致性、錯誤提示) |
| **穩健性** | 可被輔助技術解讀 (語意化 HTML) |

### 4.2 色彩對比度

| 等級 | 一般文字 | 大型文字 |
|-----|---------|---------|
| **AA** | 4.5:1 | 3:1 |
| **AAA** | 7:1 | 4.5:1 |

### 4.3 鍵盤導航

```html
<!-- 正確的焦點順序 -->
<button tabindex="0">第一個</button>
<button tabindex="0">第二個</button>

<!-- 跳過導航連結 -->
<a href="#main-content" class="skip-link">跳到主要內容</a>

<!-- ARIA 標籤 -->
<button aria-label="關閉對話框">×</button>
<nav aria-label="主導航">...</nav>
```

### 4.4 螢幕閱讀器

```html
<!-- 視覺隱藏但可被閱讀 -->
<span class="sr-only">載入中</span>

<!-- 動態內容通知 -->
<div aria-live="polite" aria-atomic="true">
  訂單已成功送出
</div>

<!-- 表單錯誤關聯 -->
<input id="email" aria-describedby="email-error">
<span id="email-error">請輸入有效的電子郵件</span>
```

---

## 5. 用戶研究方法

### 5.1 研究類型

| 類型 | 階段 | 目的 |
|-----|------|------|
| **用戶訪談** | 探索 | 深入理解需求 |
| **問卷調查** | 驗證 | 量化用戶偏好 |
| **可用性測試** | 評估 | 發現使用問題 |
| **A/B 測試** | 優化 | 驗證設計效果 |

### 5.2 可用性測試流程

```
1. 定義目標任務
2. 招募目標用戶 (5-8 人)
3. 準備測試環境
4. 執行測試 (觀察 + 錄製)
5. 分析結果 (成功率、錯誤、時間)
6. 產出改善建議
```

### 5.3 關鍵指標

| 指標 | 說明 | 目標 |
|-----|------|------|
| **任務成功率** | 完成任務的用戶比例 | > 95% |
| **任務時間** | 完成任務所需時間 | 符合預期 |
| **錯誤率** | 用戶犯錯的頻率 | < 5% |
| **SUS 分數** | 系統可用性量表 | > 80 |
| **NPS** | 淨推薦值 | > 50 |

---

## 6. 表單設計

### 6.1 表單最佳實務

| 原則 | 說明 |
|-----|------|
| **標籤位置** | 標籤置於輸入框上方 |
| **即時驗證** | 失焦時驗證，而非提交時 |
| **錯誤提示** | 顯示於欄位下方，紅色文字 |
| **必填標示** | 使用 `*` 或 "必填" 文字 |

### 6.2 錯誤處理

```html
<!-- 錯誤狀態 -->
<div class="form-field error">
  <label for="email">電子郵件 *</label>
  <input 
    id="email" 
    type="email" 
    aria-invalid="true"
    aria-describedby="email-error"
  >
  <span id="email-error" class="error-message">
    請輸入有效的電子郵件格式
  </span>
</div>
```

---

## 7. 設計檢查清單

### 視覺設計
- [ ] 色彩對比度符合 WCAG AA？
- [ ] 字體大小至少 16px？
- [ ] 間距使用 8px 網格？
- [ ] 響應式斷點正確？

### 無障礙
- [ ] 所有圖片有替代文字？
- [ ] 可完全使用鍵盤操作？
- [ ] 焦點狀態明顯可見？
- [ ] 表單有適當錯誤提示？

### 互動設計
- [ ] 觸控目標至少 44px？
- [ ] 載入狀態有回饋？
- [ ] 錯誤訊息清晰？
- [ ] 關鍵流程可逆？
