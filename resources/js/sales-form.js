export function salesForm(config) {
    let initialItemsFromDom = [];
    let customersFromDom = [];
    try {
        const initialItemsEl = document.getElementById('sale-initial-items');
        initialItemsFromDom = initialItemsEl ? JSON.parse(initialItemsEl.textContent || '[]') : [];
        
        const customersEl = document.getElementById('sale-customers');
        customersFromDom = customersEl ? JSON.parse(customersEl.textContent || '[]') : [];
    } catch (e) {
        console.error('Failed to parse JSON from DOM:', e);
    }
    
    return {
        productQuery: '',
        searchResults: [],
        searchLoading: false,
        searchTimer: null,
        items: initialItemsFromDom || (config.initialItems || []),
        discount: config.discount || 0,
        discountType: config.discountType || 'fixed',
        hasReturns: config.hasReturns || false,
        customerQuery: config.customerQuery || '',
        customerResults: config.customerResults || customersFromDom || [],
        selectedCustomerId: config.selectedCustomerId || null,
        storeCategory: config.storeCategory || '',
        customerAddress: config.customerAddress || '',
        showCustomerDropdown: false,
        searchProductsUrl: config.searchProductsUrl,

        init() {
            if (this.selectedCustomerId && this.customerResults.length > 0) {
                const selected = this.customerResults.find(c => c.id == this.selectedCustomerId);
                if (selected) {
                    if (!this.customerAddress) this.customerAddress = selected.address || '';
                    if (!this.storeCategory) this.storeCategory = selected.store_category || '';
                }
            }
        },

        // Manual toggle function that only runs when user actually clicks the checkbox
        toggleUseFefoManually(index, event) {
            event.stopImmediatePropagation();
            const item = this.items[index];
            const newValue = event.target.checked;
            
            // The x-model already updated use_fefo, so we just need to handle the batches
            if (newValue) {
                // If switching to FEFO, clear selected batches
                item.selected_batches = [];
            } else {
                // If switching to manual, start with a completely fresh empty batch row if needed
                if (item.selected_batches.length === 0) {
                    this.addBatchRow(index);
                    const firstBatch = item.selected_batches[0];
                    if (firstBatch) {
                        if (!firstBatch.batch_id && item.batches.length > 0) {
                            firstBatch.batch_id = item.batches[0].id;
                        }
                        firstBatch.quantity = item.quantity;
                    }
                }
            }
        },

        getFilteredCustomers() {
            if (!this.customerQuery || this.customerQuery.trim() === '') {
                return this.customerResults;
            }
            const q = this.customerQuery.toLowerCase();
            return this.customerResults.filter(c => 
                c.name.toLowerCase().includes(q) || 
                (c.phone && c.phone.toLowerCase().includes(q)) ||
                (c.address && c.address.toLowerCase().includes(q))
            );
        },

        selectCustomer(c) {
            this.customerQuery = c.name;
            this.selectedCustomerId = c.id;
            this.storeCategory = c.store_category || '';
            this.customerAddress = c.address || '';
            this.showCustomerDropdown = false;
        },

        async searchProducts() {
            // Require customer selection before searching
            if (!this.selectedCustomerId) { this.searchResults = []; return; }
            clearTimeout(this.searchTimer);
            const q = this.productQuery.trim();
            this.searchTimer = setTimeout(async () => {
                this.searchLoading = true;
                try {
                    let url = this.searchProductsUrl + '?q=' + encodeURIComponent(q);
                    const r = await fetch(url, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } });
                    if (!r.ok) {
                        this.searchResults = [];
                        return;
                    }
                    this.searchResults = await r.json();
                } catch (e) { this.searchResults = []; }
                this.searchLoading = false;
            }, 250);
        },

        addProduct(p) {
            if (this.hasReturns) return;
            this.items.push({
                product_id: p.id,
                name: p.name,
                sku: p.sku,
                unit_label: p.unit_label,
                in_stock: p.in_stock,
                quantity: 1,
                unit_price: p.fixed_price || 0,
                unit_discount: 0,
                unit_discount_type: 'fixed',
                discount_rate: 0,
                discount_type: 'fixed',
                requires_batch_tracking: p.requires_batch_tracking || false,
                use_fefo: true,
                selected_batches: [],
                batches: p.batches || [],
                is_free: false,
            });
            // Clear product search after selection so dropdown/modal vanishes
            this.productQuery = '';
            this.searchResults = [];
        },

        removeRow(i) {
            if (!this.hasReturns) this.items.splice(i, 1);
        },

        toggleFreeItem(index) {
            const item = this.items[index];
            if (item.is_free) {
                item.original_unit_price = item.unit_price;
                item.unit_price = 0;
                item.unit_discount = 0;
                item.discount_rate = 0;
            } else {
                item.unit_price = item.original_unit_price || 0;
            }
        },

        rowSubTotal(i) {
            const r = this.items[i];
            if (r.is_free) return 0;
            return (parseFloat(r.quantity) || 0) * (parseFloat(r.unit_price) || 0);
        },

        rowTotal(i) {
            const item = this.items[i];
            if (item.is_free) return 0;
            const sub = this.rowSubTotal(i);
            const unitDiscountType = item.unit_discount_type || 'fixed';
            const unitDiscount = Math.max(0, parseFloat(item.unit_discount) || 0);
            let unitDiscountAmount = (parseFloat(item.quantity) || 0) * unitDiscount;
            if (unitDiscountType === 'percentage') {
                const pct = Math.min(unitDiscount, 100);
                unitDiscountAmount = sub * pct / 100;
            }
            const discVal = Math.max(0, parseFloat(item.discount_rate) || 0);
            const type = item.discount_type || 'fixed';
            let remainder = Math.max(0, sub - unitDiscountAmount);
            if (type === 'percentage') {
                const pct = Math.min(discVal, 100);
                return Math.max(0, remainder - (remainder * pct / 100));
            }
            return Math.max(0, remainder - discVal);
        },

        get subtotal() {
            return this.items.reduce((a, _, i) => a + this.rowTotal(i), 0);
        },

        get receivable() {
            const discVal = Math.max(0, parseFloat(this.discount) || 0);
            if (this.discountType === 'percentage') {
                const pct = Math.min(discVal, 100);
                return Math.max(0, this.subtotal - (this.subtotal * pct / 100));
            }
            return Math.max(0, this.subtotal - discVal);
        },

        getBatchTotalQuantity(itemIndex) {
            const item = this.items[itemIndex];
            if (!item.selected_batches) return 0;
            return item.selected_batches.reduce((sum, batch) => sum + (parseFloat(batch.quantity) || 0), 0);
        },

        addBatchRow(itemIndex) {
            const item = this.items[itemIndex];
            // Find the first batch that hasn't been selected yet, or first available
            const usedBatchIds = item.selected_batches.map(sb => Number(sb.batch_id));
            let nextBatch = item.batches.find(b => !usedBatchIds.includes(Number(b.id)));
            if (!nextBatch) {
                nextBatch = item.batches[0];
            }
            item.selected_batches.push({
                batch_id: nextBatch ? nextBatch.id : null,
                quantity: 0
            });
        },

        removeBatchRow(itemIndex, batchIndex) {
            this.items[itemIndex].selected_batches.splice(batchIndex, 1);
        },

        getBatchMeta(item, batchId) {
            if (!batchId || !item.batches) return null;
            return item.batches.find(b => Number(b.id) === Number(batchId)) || null;
        },

        getBatchAvailableForRow(itemIndex, batchIndex) {
            const item = this.items[itemIndex];
            const row = item.selected_batches[batchIndex];
            if (!row?.batch_id) return 0;
            const meta = this.getBatchMeta(item, row.batch_id);
            if (!meta) return 0;
            const usedElsewhere = item.selected_batches.reduce((sum, sb, idx) => {
                if (idx === batchIndex) return sum;
                if (Number(sb.batch_id) === Number(row.batch_id)) {
                    return sum + (parseFloat(sb.quantity) || 0);
                }
                return sum;
            }, 0);
            return Math.max(0, (parseFloat(meta.quantity_available) || 0) - usedElsewhere);
        },

        batchQuantityExceedsAvailable(itemIndex, batchIndex) {
            const row = this.items[itemIndex].selected_batches[batchIndex];
            const qty = parseFloat(row?.quantity) || 0;
            if (qty <= 0) return false;
            return qty > this.getBatchAvailableForRow(itemIndex, batchIndex) + 0.0001;
        },

        batchQuantityErrorMessage(itemIndex, batchIndex) {
            if (!this.batchQuantityExceedsAvailable(itemIndex, batchIndex)) return '';
            const available = this.getBatchAvailableForRow(itemIndex, batchIndex);
            const qty = parseFloat(this.items[itemIndex].selected_batches[batchIndex]?.quantity) || 0;
            return `Quantity (${qty}) exceeds available (${available}) for this batch.`;
        },

        // New methods for quantity validation
        quantityExceedsAvailable(itemIndex) {
            const item = this.items[itemIndex];
            const available = parseFloat(item.in_stock) || 0;
            if (available <= 0) return false;
            // Sum quantities of ALL rows for the same product (paid + free combined)
            const totalQty = this.items
                .filter(i => i.product_id === item.product_id)
                .reduce((sum, i) => sum + (parseFloat(i.quantity) || 0), 0);
            return totalQty > available + 0.0001;
        },

        quantityErrorMessage(itemIndex) {
            if (!this.quantityExceedsAvailable(itemIndex)) return '';
            const item = this.items[itemIndex];
            const available = parseFloat(item.in_stock) || 0;
            const totalQty = this.items
                .filter(i => i.product_id === item.product_id)
                .reduce((sum, i) => sum + (parseFloat(i.quantity) || 0), 0);
            const otherRows = this.items.filter((i, idx) => i.product_id === item.product_id && idx !== itemIndex).length;
            const suffix = otherRows > 0 ? ` (combined across ${otherRows + 1} rows)` : '';
            return `Total qty (${totalQty})${suffix} exceeds available stock (${available}).`;
        },

        hasManualBatchErrors() {
            return this.items.some((item, itemIndex) => {
                if (!item.requires_batch_tracking || item.use_fefo) return false;

                // 1. Check if any selected batch row exceeds available stock
                if (item.selected_batches && item.selected_batches.some((_, batchIndex) => this.batchQuantityExceedsAvailable(itemIndex, batchIndex))) {
                    return true;
                }

                // 2. Check if total quantity allocated from batches matches the required item quantity
                const batchTotal = this.getBatchTotalQuantity(itemIndex);
                const reqQty = parseFloat(item.quantity) || 0;
                if (Math.abs(batchTotal - reqQty) > 0.0001) {
                    return true;
                }

                return false;
            });
        },

        hasQuantityErrors() {
            return this.items.some((_, itemIndex) => this.quantityExceedsAvailable(itemIndex));
        },

        hasAnyErrors() {
            return this.hasManualBatchErrors() || this.hasQuantityErrors();
        },
    };
}

export function saleReturnForm(config) {
    let initialLinesFromDom = [];
    try {
        const initialLinesEl = document.getElementById('sale-return-initial-lines');
        initialLinesFromDom = initialLinesEl ? JSON.parse(initialLinesEl.textContent || '[]') : [];
    } catch (e) {
        console.error('Failed to parse JSON from DOM:', e);
    }

    const normalizeBatch = (batch) => ({
        ...batch,
        good_quantity: parseFloat(batch.good_quantity) || 0,
        damaged_quantity: parseFloat(batch.damaged_quantity) || 0,
    });

    const processedItems = (config.initialItems || initialLinesFromDom || []).map(item => ({
        ...item,
        good_quantity: parseFloat(item.good_quantity) || 0,
        damaged_quantity: parseFloat(item.damaged_quantity) || 0,
        batches: (item.batches || []).map(normalizeBatch),
    }));

    const lineReturnQty = (item) => {
        if (!item.requires_batch_tracking || item.use_fefo) {
            return (parseFloat(item.good_quantity) || 0) + (parseFloat(item.damaged_quantity) || 0);
        }

        return (item.batches || []).reduce(
            (sum, batch) => sum + (parseFloat(batch.good_quantity) || 0) + (parseFloat(batch.damaged_quantity) || 0),
            0
        );
    };

    const batchReturnQty = (batch) => (parseFloat(batch.good_quantity) || 0) + (parseFloat(batch.damaged_quantity) || 0);

    return {
        items: processedItems,
        discount: config.discount || 0,
        restockingFee: config.restockingFee || 0,

        init() {
            this.items = this.items.map(item => ({
                ...item,
                good_quantity: parseFloat(item.good_quantity) || 0,
                damaged_quantity: parseFloat(item.damaged_quantity) || 0,
                batches: (item.batches || []).map(normalizeBatch),
            }));
        },

        rowTotal(i) {
            return lineReturnQty(this.items[i]) * (parseFloat(this.items[i].unit_price) || 0);
        },

        get subtotal() {
            return this.items.reduce((a, _, i) => a + this.rowTotal(i), 0);
        },

        get payableToCustomer() {
            return Math.max(0, this.subtotal - (parseFloat(this.discount) || 0) - (parseFloat(this.restockingFee) || 0));
        },

        get anyItemsSelected() {
            return this.items.some(item => lineReturnQty(item) > 0);
        },

        itemTotalReturning(item) {
            return lineReturnQty(item);
        },

        itemGoodQty(item) {
            if (!item.requires_batch_tracking || item.use_fefo) {
                return parseFloat(item.good_quantity) || 0;
            }
            return (item.batches || []).reduce((sum, b) => sum + (parseFloat(b.good_quantity) || 0), 0);
        },

        itemDamagedQty(item) {
            if (!item.requires_batch_tracking || item.use_fefo) {
                return parseFloat(item.damaged_quantity) || 0;
            }
            return (item.batches || []).reduce((sum, b) => sum + (parseFloat(b.damaged_quantity) || 0), 0);
        },

        fillAllGood(item) {
            if (!item.requires_batch_tracking || item.use_fefo) {
                item.good_quantity = parseFloat(item.returnable_quantity) || 0;
                item.damaged_quantity = 0;
                return;
            }
            (item.batches || []).forEach(batch => {
                batch.good_quantity = parseFloat(batch.returnable_quantity) || 0;
                batch.damaged_quantity = 0;
            });
        },

        fillAllDamaged(item) {
            if (!item.requires_batch_tracking || item.use_fefo) {
                item.damaged_quantity = parseFloat(item.returnable_quantity) || 0;
                item.good_quantity = 0;
                return;
            }
            (item.batches || []).forEach(batch => {
                batch.damaged_quantity = parseFloat(batch.returnable_quantity) || 0;
                batch.good_quantity = 0;
            });
        },

        clearReturn(item) {
            if (!item.requires_batch_tracking || item.use_fefo) {
                item.good_quantity = 0;
                item.damaged_quantity = 0;
                return;
            }
            (item.batches || []).forEach(batch => {
                batch.good_quantity = 0;
                batch.damaged_quantity = 0;
            });
        },

        isOverLimit(item) {
            return lineReturnQty(item) > (parseFloat(item.returnable_quantity) + 0.0001);
        },

        isBatchOverLimit(batch) {
            return batchReturnQty(batch) > (parseFloat(batch.returnable_quantity) + 0.0001);
        },

        get hasErrors() {
            return this.items.some(item => {
                if (this.isOverLimit(item)) return true;
                if (item.requires_batch_tracking && !item.use_fefo) {
                    return (item.batches || []).some(batch => this.isBatchOverLimit(batch));
                }
                return false;
            });
        },
    };
}
